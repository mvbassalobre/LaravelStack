@extends("templates.admin")
@section('title',$resource->title)
@section('content')
@include("templates.alerts")
<div class="row">
    <div class="col-12">
        <div class="d-flex flex-row justify-content-between mb-3">
            <h4>{!! @$resource->icon !!} {{$resource->plural_title}}</h4>
            <button class="btn btn-primary btn-sm-block">
                <span class="el-icon-plus mr-2"></span>Cadastrar {{$resource->title}}
            </button>
        </div>
        <div class="card">
            <div class="card-header">
                @include("admin.resources.partials._filter")
            </div>
            <div class="card-body p-0">
                @include("admin.resources.partials._table")
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div>Resultados encontrados : {{ $data->total() }}</div>
                    <div class="float-right">
                        {{ $data->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection