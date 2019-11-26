@extends("templates.admin")
@section('title',$resource->label())
@section('content')
@include("templates.alerts")
<div class="row">
    <div class="col-12">
        @include("templates.alerts")
        <nav aria-label="breadcrumb">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('admin.home')}}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{$resource->label()}}</li>
                </ol>
            </nav>
        </nav>
        <div class="d-flex flex-row justify-content-between mb-3">
            <h4>{!! @$resource->icon() !!} Listagem de {{$resource->label()}}</h4>
            @if($resource->canCreate())
            <a class="btn btn-primary btn-sm-block text-white cursor-pointer" href="{{route('resource.create',['resource'=>$resource->id])}}">
                    <span class="el-icon-plus mr-2"></span>Cadastrar {{$resource->singularLabel()}}
                </a>
            @endif
        </div>
        
        <div class="card">
            <div class="card-header">
                @include("admin.vStack.resources.partials._filter")
            </div>
            <div class="card-body p-0">
                @include("admin.vStack.resources.partials._table")
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div>Resultados encontrados : {{ $data->total() }}</div>
                    <div class="float-right">
                        {{$data->appends(request()->query())->links()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection