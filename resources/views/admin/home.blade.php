@extends("templates.admin")
@section('title',"Home")
@section('content')
@include("templates.alerts")
<nav aria-label="breadcrumb">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active"><a href="{{route('admin.home')}}">Dashboard</a></li>
        </ol>
    </nav>
</nav>
<h1>HOME</h1>
@endsection