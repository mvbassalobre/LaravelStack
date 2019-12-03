<?php

namespace App\vStack\Controllers;

use App\Http\Controllers\Controller;
use ResourcesHelpers;
use Illuminate\Http\Request;
use App\vStack\Services\Messages;
use Auth;

class ResourceController extends Controller
{
    public function index($resource, Request $request)
    {
        $resource = ResourcesHelpers::find($resource);
        if (!$resource->canViewList()) abort(403);
        $data = $this->getPaginatedData($resource, $request);
        return view("vStack::resources.index", compact("resource", "data"));
    }

    private function getPaginatedData($resource, Request $request)
    {
        $data      = $request->all();
        $orderBy   = @$data["order_by"] ? $data["order_by"] : "id";
        $orderType = @$data["order_type"] ? $data["order_type"] : "desc";
        $perPage   = $resource->resultsPerPage();
        $query     = $resource->model->orderBy($orderBy, $orderType);
        foreach ($resource->filters() as $filter) $query = $filter->applyFilter($query, $data);
        foreach ($resource->search() as $search) {
            $query = $query->where($search, "like", "%" . (@$data["_"] ? $data["_"] : "") . "%");
        }
        foreach ($resource->lens() as $len) {
            $field = $len["field"];
            if (isset($data[$field])) {
                $value = $data[$field];
                $query = $query->where($field, $value);
            }
        }
        $query = $query->paginate($perPage);
        return $query;
    }

    public function create($resource)
    {
        $resource = ResourcesHelpers::find($resource);
        if (!$resource->canCreate()) abort(403);
        $data = $this->makeCrudData($resource);
        $data["page_type"] = "Cadastro";
        return view("vStack::resources.crud", compact("resource", "data"));
    }

    public function import($resource)
    {
        $resource = ResourcesHelpers::find($resource);
        if (!($resource->canImport() && $resource->canCreate())) abort(403);
        $data = $this->makeImportData($resource);
        return view("vStack::resources.import", compact('data'));
    }

    private function makeImportData($resource)
    {
        $columns = $resource->model->getConnection()->getSchemaBuilder()->getColumnListing($resource->model->getTable());
        $columns = array_filter($columns,function($x)
        {
            if(!in_array($x,["id","created_at","deleted_at","updated_at","email_verified_at","confirmation_token","recovery_token","password"])) return $x;
        });
        return [
            "resource" => [
                "label"          => $resource->label(),
                "singular_label" => $resource->singularLabel(),
                "route"          => $resource->route(),
                "columns"        => $columns
            ]
        ];
    }

    public function checkFileImport($resource,Request $request)
    {
        $resource = ResourcesHelpers::find($resource);
        if (!($resource->canImport() && $resource->canCreate())) abort(403);
        $file = $request->file("file");
        $delimiter = $request["delimiter"];
        if(!$file) return ["success" => false, "message" => ["type" => "error", "text" => "Arquivo inválido..."]];
        if($file->getSize()>137072) return ["success" => false, "message" => ["type" => "error", "text" => "Arquivo maior do que o permitido..."]];
        $csvFile = file($file->getPathName());
        if(!@$csvFile[0]) return ["success" => false, "message" => ["type" => "error", "text" => "Arquivo inválido..."]];
        $header = str_getcsv($csvFile[0],$delimiter);
        return ["success" => true, "data" => $header];
    } 

    public function importSubmit($resource,Request $request)
    {
        $resource = ResourcesHelpers::find($resource);
        if (!($resource->canImport() && $resource->canCreate())) abort(403);
        $data = $request->all();
        $file = $data["file"];
        if(!$file) return ["success" => false, "message" => ["type" => "error", "text" => "Arquivo inválido..."]];
        if($file->getSize()>137072) return ["success" => false, "message" => ["type" => "error", "text" => "Arquivo maior do que o permitido..."]];
        $csvFile = file($file->getPathName());
        if(!@$csvFile[0]) return ["success" => false, "message" => ["type" => "error", "text" => "Arquivo inválido..."]];
        $data["config"] = json_decode($data["config"]);
        $this->importCSV($resource,$csvFile,$data["config"]);
        return ["success"=>true];
    } 

    private function importCSV($resource,$rows,$config)
    {
        $user = Auth::user();
        dispatch(function () use ($user,$rows,$config,$resource) {
            $headers = $config->data->csv_header;
            $data = [];
            for($i=1;$i<count($rows);$i++)
            {
                $_data = [];
                $columns = str_getcsv($rows[$i],$config->delimiter);
                for($y=0;$y<count($headers);$y++) $_data[$headers[$y]] = $columns[$y];
                if( $_data) $data[] = $_data;
            }
            $fieldlist = $config->fieldlist;
            $errors = 0;
            $count = 0;
            foreach($data as $row)
            {
                $new = [];
                foreach($fieldlist as $key=>$value)
                {
                    if($value!="_IGNORE_") $new[$value] = $row[$key];
                }
                try {
                    $resource->model->create($new);
                    $count++;
                } catch(\Exception $e) {
                    $errors ++;
                }
            }
            if($count > 0) Messages::notify("success", $count." ".($count>1 ? $resource->label()." importado" : $resource->singularLabel()." importados")." com sucesso !!", $user->id);
            if($errors > 0) Messages::notify("danger", $errors." ".($errors>1 ? $resource->label()." não pode" : $resource->singularLabel()." não puderam")." ser importados !!", $user->id);
        });
    }

    public function edit($resource, $code)
    {
        $resource = ResourcesHelpers::find($resource);
        if (!$resource->canUpdate()) abort(403);
        $content = $resource->model->findOrFail($code);
        $data = $this->makeCrudData($resource, $content);
        $data["page_type"] = "Edição";
        return view("vStack::resources.crud", compact("resource", "data"));
    }

    public function destroy($resource, $code)
    {
        $resource = ResourcesHelpers::find($resource);
        if (!$resource->canDelete()) abort(403);
        $content = $resource->model->findOrFail($code);
        if ($content->delete()) {
            Messages::send("success", $resource->singularLabel() . " Excluido com sucesso !!");
            return ["success" => true, "route" => $resource->route()];
        }
        Messages::send("error", " Erro ao excluir com " . $resource->singularLabel() . " !!");
        return ["success" => false,  "route" => $resource->route()];
    }

    public function view($resource, $code)
    {
        $resource = ResourcesHelpers::find($resource);
        if (!$resource->canView()) abort(403);
        $content = $resource->model->findOrFail($code);
        $data = $this->makeViewData($content->code, $resource, $content);
        $data["page_type"] = "Visualização";
        return view("vStack::resources.view", compact("resource", "data"));
    }

    private function makeViewData($code, $resource, $content = null)
    {
        $route = $resource->route();
        return [
            "fields"        => $this->makeViewDataFields($content, $resource->fields()),
            "can_update"    => $resource->canUpdate(),
            "can_delete"    => $resource->canDelete(),
            "update_route"  => $route . "/" . $code . "/edit",
            "route_destroy" => $route . "/" . $code . "/destroy",
        ];
    }

    private function makeViewDataFields($content, $fields)
    {
        $data = [];
        if (!$content) return $fields;
        foreach ($fields  as $field) {
            switch ($field->options["type"]) {
                case "text":
                    $data[$field->options["label"]] = @$content->{$field->options["field"]};
                    break;
                case "check":
                    $data[$field->options["label"]] = @$content->{$field->options["field"]} ? '<span class="badge badge-success">Sim</span>' : '<span class="badge badge-danger">Não</span>';
                    break;
                case "belongsTo":
                    $model = $field->options["model"];
                    $value = app()->make($model)->findOrFail($content->{$field->options["field"]})->name;
                    $data[$field->options["label"]] = $value;
                    break;
                default:
                    $data[$field->options["label"]] = @$content->{$field->options["field"]};
                    break;
            }
        }
        return $data;
    }

    private function makeCrudData($resource, $content = null)
    {
        return [
            "id"          => @$content->id,
            "fields"      => $this->makeCrudDataFields($content, $resource->fields()),
            "store_route" => route('resource.store'),
            "list_route"  => route('resource.index', ["resource" => $resource->id]),
            "resource_id" => $resource->id
        ];
    }

    private function makeCrudDataFields($content, $fields)
    {
        if (!$content) return $fields;
        foreach ($fields  as $field) {
            $field->options["value"] = @$content->{$field->options["field"]};
        }
        return $fields;
    }

    public function store(Request $request)
    {
        $data = $request->all();
        if (!@$data["resource_id"]) abort(404);
        $resource = ResourcesHelpers::find($data["resource_id"]);
        if (@$data["id"]) if (!$resource->canUpdate()) abort(403);
        if (!@$data["id"]) if (!$resource->canCreate()) abort(403);
        $this->validate($request, $resource->getValidationRule());
        $target = @$data["id"] ? $resource->model->findOrFail($data["id"]) : new $resource->model();

        $data = $request->except(["resource_id", "id"]);
        $target->fill($data);
        $target->save();
        Messages::send("success", $resource->singularLabel() . " Salvo com sucesso !!");
        return ["success" => true, "route" => route('resource.index', ["resource" => $resource->id])];
    }

    public function option_list(Request $request)
    {
        $model = app()->make($request["model"]);
        return ["success" => true, "data" => $model->select("id", "name")->get()];
    }

    public function globalSearch(Request $request)
    {
        $data = [];
        $filter = $request["filter"];
        foreach (ResourcesHelpers::all() as $resource) {
            $keys = array_keys($resource);
            $resource = $resource[$keys[0]];
            if ($resource->globallySearchable() && $resource->canView()) {
                $search_indexes = $resource->search();
                $query = $resource->model->where("id", ">", 0);
                foreach ($search_indexes as $si) $query = $query->where($si, "like", "%" . $filter . "%");
                $label = $resource->singularLabel();
                foreach ($query->get() as $row) {
                    $data[] = [
                        "resource" => $label,
                        "name"     => $row->name,
                        "link"     => $resource->route() . "/" . $row->code
                    ];
                }
            }
        }
        return ["data" => $data];
    }
}
