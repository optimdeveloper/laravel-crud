@extends('layouts.master')

@section('title') @lang('translation.Users') @endsection

@section('css')
<!-- DataTables -->
<link href="{{ URL::asset('/assets/libs/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('/assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

    @component('common-components.breadcrumb')
        @slot('pagetitle') @lang('translation.Users') @endslot
        @slot('title') @lang('translation.Users') @endslot
    @endcomponent

        <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <h4 class="card-title col-sm-4">
                            @lang('translation.Users')
                        </h4>
                        <div class="col-sm-4">
                        </div>
                        <div class="col-sm-4">
                            @if (session('delete') == 'ok')
                                <div class="alert alert-border alert-border-success alert-dismissible fade show" role="alert">
                                    <i class="uil uil-check font-size-16 text-success me-2"></i>
                                    User successfully deleted
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">

                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                    <hr/>
                    <div class="row">
                        <table id="datatable"
                               class="table table-bordered dt-responsive nowrap w-100 align-middle">
                            <thead class="table-light">
                            <tr class="text-center">
                                <th>@lang('translation.Id')</th>
                                <th>@lang('translation.Name')</th>
                                <th>@lang('translation.Email')</th>
                                <th>@lang('translation.Phone')</th>
                                <th>@lang('translation.Birthday')</th>
                                <th>@lang('translation.Gender')</th>
                                <th class="th45 no-sort">@lang('translation.Delete')</th>
                                <th class="th45 no-sort">@lang('translation.Detail')</th>
                            </tr>
                            </thead>
                            {{-- <tbody>
                            @foreach ($data as $item)
                                <tr class="text-center">
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->email }}</td>
                                    <td>{{ $item->phone_number }}</td>
                                    <td>{{ Str::substr($item->birthday_at, 0, 10) }}</td>
                                    <td>{{ $item->gender }}</td>
                                    <td>
                                        <a href="{{route("user.delete", $item->id)}}" class="text-danger sweet-warning delete" ><i
                                                class="mdi mdi-delete font-size-18" id="sa-warning"></i></a>
                                    </td>
                                    <td>
                                        <a href="{{route("user.detail", $item->id)}}" class="text-success"><i
                                                class="mdi mdi-account-details-outline font-size-18"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody> --}}
                        </table>
                    </div>
                </div>
            </div>
        </div> <!-- end col -->




@endsection

@section('script')

<script src="{{ URL::asset('/assets/libs/datatables/datatables.min.js') }}"></script>
<script src="{{ URL::asset('/assets/js/pages/datatables.init.js') }}"></script>
<script src="{{ URL::asset('/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('/assets/js/pages/sweet-alerts.init.js') }}"></script>

<!-- tables -->
<script src="{{ URL::asset('/assets/js/pages/table-users.init.js?') . config('app.version') }}"></script>
@endsection



