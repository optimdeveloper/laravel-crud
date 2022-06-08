@extends('layouts.master-vertical')
@section('title') @lang('translation.Vertical') @endsection
@section('content')
    @component('common-components.breadcrumb')
        @slot('pagetitle') Myngly @endslot
        @slot('title') Dashboard @endslot
    @endcomponent
@endsection
@section('script')

@endsection
