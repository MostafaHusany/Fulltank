<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\ClientCategory;
use App\Models\ClientDocument;
use App\Services\UserService;
use App\Services\ClientDocumentService;

use App\Http\Traits\ResponseTemplate;

class ClientController extends Controller
{
    use ResponseTemplate;

    private $targetModel;
    private $userService;
    private $documentService;

    public function __construct(UserService $userService, ClientDocumentService $documentService)
    {
        $this->targetModel = new User;
        $this->userService = $userService;
        $this->documentService = $documentService;
    }

    public function index(Request $request)
    {
        $permissions = auth()->user()->category == 'admin'
            ? 'admin'
            : $this->getPermissions(['clients_add', 'clients_edit', 'clients_delete', 'clients_show']);

        if ($request->ajax()) {
            $model = $this->targetModel->query()
                ->where('category', 'client')
                ->with(['clientCategory', 'wallet'])
                ->withCount(['clientDocuments', 'vehicles', 'drivers'])
                ->orderBy('id', 'desc')
                ->adminFilter();

            $datatable_model = Datatables::of($model)
                ->addColumn('checkbox_selector', function ($row_object) use ($permissions) {
                    return view('layouts.admin.incs._checkbox_selector', compact('row_object'));
                })
                ->addColumn('client_category_name', function ($row_object) {
                    return $row_object->clientCategory ? e($row_object->clientCategory->name) : '---';
                })
                ->addColumn('current_balance', function ($row_object) {
                    $balance = $row_object->wallet ? (float) $row_object->wallet->valide_balance : 0;
                    return number_format($balance, 2);
                })
                ->addColumn('vehicles_hub', function ($row_object) {
                    return view('admin.clients.incs._vehicles_hub', compact('row_object'));
                })
                ->addColumn('drivers_hub', function ($row_object) {
                    return view('admin.clients.incs._drivers_hub', compact('row_object'));
                })
                ->addColumn('documents_btn', function ($row_object) use ($permissions) {
                    return view('admin.clients.incs._documents_btn', compact('row_object', 'permissions'));
                })
                ->addColumn('activation', function ($row_object) use ($permissions) {
                    return view('admin.clients.incs._active', compact('row_object', 'permissions'));
                })
                ->addColumn('actions', function ($row_object) use ($permissions) {
                    return view('admin.clients.incs._actions', compact('row_object', 'permissions'));
                });

            return $datatable_model->make(true);
        }

        return view('admin.clients.index', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'            => 'required|max:255',
            'company_name'    => 'required|max:255',
            'client_category' => 'required',
            'email'           => 'required|email|unique:users,email|max:255',
            'phone'           => 'required|max:255|unique:users,phone',
            'password'        => 'required|min:8',
            'picture'         => 'nullable|image|max:10240',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        $client_category_id = $this->resolveClientCategoryId($request->client_category);
        if ($client_category_id === null) {
            return $this->responseTemplate(null, false, ['client_category' => [__('clients.client_category_required')]]);
        }

        $data = $request->only(['name', 'company_name', 'email', 'phone', 'password']);
        $data['client_category_id'] = $client_category_id;

        try {
            DB::beginTransaction();

            $picture_path = $this->userService->handleClientPicture($request);
            if ($picture_path) {
                $data['picture'] = $picture_path;
            }

            $user = $this->userService->createClient($data);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('ClientController@store Exception', ['error' => $exception->getMessage()]);
            return $this->responseTemplate(null, false, [__('clients.object_error')]);
        }

        return $this->responseTemplate($user, true, __('clients.object_created'));
    }

    public function show($id)
    {
        $user = $this->targetModel->query()
            ->where('category', 'client')
            ->with('clientCategory')
            ->find($id);

        if (!isset($user)) {
            return $this->responseTemplate(null, false, __('clients.object_not_found'));
        }

        $data = $user->toArray();
        $data['client_category_name'] = $user->clientCategory ? $user->clientCategory->name : null;
        return $this->responseTemplate($data, true, null);
    }

    public function update(Request $request, $id)
    {
        $user = $this->targetModel->query()
            ->where('category', 'client')
            ->find($id);

        if (!isset($user)) {
            return $this->responseTemplate(null, false, __('clients.object_not_found'));
        }

        return isset($request->activate_object)
            ? $this->activateClient($user)
            : $this->updateClient($request, $user);
    }

    public function destroy(Request $request, $id)
    {
        return $id == 0 && isset($request->selected_ids)
            ? $this->bulkDelete($request)
            : $this->delete($id);
    }

    public function dataAjax(Request $request)
    {
        $search = $request->q;
        $query = $this->targetModel->query()
            ->select('id', 'name', 'company_name', 'client_category_id', 'phone', 'email', 'category')
            ->where('category', 'client')
            ->where(function ($q) use ($search) {
                $q->orWhere('name', 'like', "%{$search}%");
                $q->orWhere('company_name', 'like', "%{$search}%");
                $q->orWhere('email', 'like', "%{$search}%");
                $q->orWhere('phone', 'like', "%{$search}%");
            });

        return response()->json($query->get());
    }

    public function categoriesAjax(Request $request)
    {
        $search = $request->q;
        $query = ClientCategory::query()->orderBy('name');
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        return response()->json($query->get(['id', 'name']));
    }

    /**
     * List client documents (AJAX). Admin/technical only via middleware.
     */
    public function indexDocuments($clientId)
    {
        $this->ensureAuthorized();
        $client = $this->targetModel->query()->where('category', 'client')->find($clientId);
        if (!$client) {
            return $this->responseTemplate(null, false, __('clients.object_not_found'));
        }
        $documents = $client->clientDocuments()->orderBy('id', 'desc')->get();
        return $this->responseTemplate($documents, true, null);
    }

    /**
     * Store client document. Admin/technical only.
     */
    public function storeDocument(Request $request, $clientId)
    {
        $this->ensureAuthorized();
        $client = $this->targetModel->query()->where('category', 'client')->find($clientId);
        if (!$client) {
            return $this->responseTemplate(null, false, __('clients.object_not_found'));
        }
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'file'  => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);
        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }
        try {
            DB::beginTransaction();
            $doc = $this->documentService->store($client, $request);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('ClientController@storeDocument Exception', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, [__('clients.document_upload_error')]);
        }
        if (!$doc) {
            return $this->responseTemplate(null, false, [__('clients.document_upload_error')]);
        }
        return $this->responseTemplate($doc, true, __('clients.document_uploaded'));
    }

    /**
     * View file inline (new tab). Protected route.
     */
    public function viewDocument($documentId)
    {
        $this->ensureAuthorized();
        $doc = ClientDocument::with('client')->find($documentId);
        if (!$doc || $doc->client->category !== 'client') {
            abort(404, 'Document not found.');
        }
        $path = $this->documentService->fullPath($doc);
        if (!file_exists($path)) {
            abort(404, 'File not found.');
        }
        return response()->file($path);
    }

    /**
     * Download file. Protected route.
     */
    public function downloadDocument($documentId)
    {
        $this->ensureAuthorized();
        $doc = ClientDocument::with('client')->find($documentId);
        if (!$doc || $doc->client->category !== 'client') {
            abort(404, 'Document not found.');
        }
        $path = $this->documentService->fullPath($doc);
        if (!file_exists($path)) {
            abort(404, 'File not found.');
        }
        $filename = $doc->title . '.' . $doc->extension;
        return response()->download($path, $filename);
    }

    /**
     * Delete document.
     */
    public function destroyDocument($documentId)
    {
        $this->ensureAuthorized();
        $doc = ClientDocument::with('client')->find($documentId);
        if (!$doc || $doc->client->category !== 'client') {
            return $this->responseTemplate(null, false, __('clients.object_not_found'));
        }
        try {
            $this->documentService->delete($doc);
        } catch (Exception $e) {
            Log::error('ClientController@destroyDocument Exception', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, [__('clients.document_delete_error')]);
        }
        return $this->responseTemplate(null, true, __('clients.document_deleted'));
    }

    private function ensureAuthorized(): void
    {
        $user = auth()->user();
        if (!$user || !in_array($user->category, ['admin', 'technical'])) {
            abort(403, 'Unauthorized.');
        }
    }

    /**
     * Resolve client_category from request: existing ID or new string (creates category and returns id).
     */
    private function resolveClientCategoryId($value): ?int
    {
        if (empty($value)) {
            return null;
        }
        if (is_numeric($value)) {
            $cat = ClientCategory::find((int) $value);
            return $cat ? $cat->id : null;
        }
        $name = is_string($value) ? trim($value) : null;
        if (empty($name)) {
            return null;
        }
        $cat = ClientCategory::firstOrCreate(['name' => $name]);
        return $cat->id;
    }

    // HELPERS
    private function updateClient(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name'            => 'required|max:255',
            'company_name'    => 'required|max:255',
            'client_category' => 'required',
            'email'           => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone'           => 'required|max:255|unique:users,phone,' . $user->id,
            'password'        => 'nullable|min:8',
            'picture'         => 'nullable|image|max:10240',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        $client_category_id = $this->resolveClientCategoryId($request->client_category);
        if ($client_category_id === null) {
            return $this->responseTemplate(null, false, ['client_category' => [__('clients.client_category_required')]]);
        }

        $data = $request->only(['name', 'company_name', 'email', 'phone']);
        $data['client_category_id'] = $client_category_id;

        try {
            DB::beginTransaction();

            $picture_path = $this->userService->handleClientPicture($request);
            if ($picture_path) {
                $data['picture'] = $picture_path;
            }

            $this->userService->updateClient($user, array_merge($data, $request->only('password')));

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('ClientController@update Exception', ['error' => $exception->getMessage()]);
            return $this->responseTemplate(null, false, [__('clients.object_error')]);
        }

        return $this->responseTemplate($user->fresh(), true, __('clients.object_updated'));
    }

    private function activateClient(User $user)
    {
        try {
            DB::beginTransaction();

            $user->is_active = !$user->is_active;
            $user->save();

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->responseTemplate(null, false, [__('clients.object_error')]);
        }

        return $this->responseTemplate($user, true, __('clients.object_updated'));
    }

    private function bulkDelete(Request $request)
    {
        $ids = is_array($request->selected_ids) ? $request->selected_ids : explode(',', $request->selected_ids);
        $this->targetModel->query()
            ->where('category', 'client')
            ->whereIn('id', $ids)
            ->delete();

        return $this->responseTemplate(null, true, __('clients.object_deleted'));
    }

    private function delete($id)
    {
        $user = $this->targetModel->query()
            ->where('category', 'client')
            ->find($id);

        if (!isset($user)) {
            return $this->responseTemplate(null, false, __('clients.object_not_found'));
        }

        $user->delete();

        return $this->responseTemplate($user, true, __('clients.object_deleted'));
    }
}
