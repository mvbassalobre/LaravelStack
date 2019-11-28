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
                    <li class="breadcrumb-item"><a href="{{route('admin.home')}}" class="link">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{$resource->route()}}" class="link">{{$resource->label()}}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{$data["page_type"]}} de {{$resource->singularLabel()}}</li>
                </ol>
            </nav>
        </nav>
        <div class="d-flex flex-row justify-content-between mb-3">
            <h4>{!! @$resource->icon() !!} {{$data["page_type"]}} de {{$resource->singularLabel()}}</h4>
        </div>
    </div>
</div>
@endsection