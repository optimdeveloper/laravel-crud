@extends('layouts.master')

@section('title') @lang('translation.Users') @endsection

@section('css')
    <!-- DataTables -->
    <link href="{{ URL::asset('/assets/libs/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ URL::asset('/assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('content')

    @component('common-components.breadcrumb')
        @slot('pagetitle') @lang('translation.Events') @endslot
        @slot('title') @lang('translation.Events') @endslot
    @endcomponent

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <h4 class="card-title col-sm-4">
                            @lang('translation.Events')
                        </h4>
                        <div class="col-sm-4">
                        </div>
                        <div class="col-sm-4">
                            @if (session('cancel') == 'ok')
                                <div class="alert alert-border alert-border-success alert-dismissible fade show"
                                     role="alert">
                                    <i class="uil uil-check font-size-16 text-success me-2"></i>
                                    Event successfully Toggle Published
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
                                <th>@lang('translation.Datetime')</th>
                                <th>@lang('translation.Location')</th>
                                <th>@lang('translation.Privacy')</th>
                                <th>@lang('translation.Published')</th>
                                <th>@lang('translation.Focused_gender')</th>
                                <th>@lang('translation.Focused_age')</th>
                                <th class="th45 no-sort">@lang('Toggle Published')</th>
                                <th class="th45 no-sort">@lang('translation.Detail')</th>
                            </tr>
                            </thead>
                            {{-- <tbody>
                            @foreach ($data as $item)
                                <tr class="text-center">
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ Str::substr($item->date_time, 0, 10) }}</td>
                                    <td>{{ $item->location }}</td>
                                    <td>{{ $item->privacy }}</td>
                                    <td>@if ($item->published == 1) True @else False @endif</td>
                                    <td>{{ $item->focused_on_gender }}</td>
                                    <td>{{ $item->focused_on_age_range }}</td>
                                    <td>
                                        <a href="{{route("event.cancel", $item->id)}}" class="text-danger sweet-warning delete" ><i
                                                class="mdi mdi-book-sync font-size-18" id="sa-warning"></i></a>
                                    </td>
                                    <td>
                                        <a href="{{route("event.detail", $item->id)}}" class="text-success"><i
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
            <script src="{{ URL::asset('/assets/js/pages/table-events.init.js?') . config('app.version') }}"></script>
@endsection



