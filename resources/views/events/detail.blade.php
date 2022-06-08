@extends('layouts.master')

@section('title') @lang('translation.Users') @endsection

@section('content')

    @component('common-components.breadcrumb')
        @slot('pagetitle') @lang('translation.Events') @endslot
        @slot('title') @lang('translation.Detail') @endslot
    @endcomponent


    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title col-sm-4">
                        @lang('translation.Detail'): {{ $Event[0]->name }}
                    </h4>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 offset-md-3">
                            <div class="mt-4">
                                <form>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label" for="formrow-password-input">Photo</label>
                                                <img src="{{ $Event[0]->photo_event->path }}" alt="EventPhoto" srcset="" style="object-fit: cover; width: 100%; height: 200px;">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="formrow-email-input">Event Name</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="formrow-email-input" value="{{ $Event[0]->name }}" disabled>
                                                    <div class="input-group-text"><i class="mdi mdi-clipboard-file-outline"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="formrow-password-input">Hostname</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="formrow-password-input" value="{{ $Event[0]->user->name }}" disabled>
                                                    <div class="input-group-text"><i class="mdi mdi-account-circle-outline"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label" for="formrow-firstname-input">Location</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="formrow-firstname-input" value="{{ $Event[0]->location }}" disabled>
                                                    <div class="input-group-text"><i class="mdi mdi-map-marker-check-outline"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label" for="formrow-firstname-input">Description</label>
                                                <div class="input-group">
                                                    <textarea class="form-control" id="formrow-firstname-input" disabled>{{ $Event[0]->description }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="formrow-email-input">DateTime</label>
                                                <div class="input-group">
                                                    <input type="email" class="form-control" id="formrow-email-input" value="{{ Str::substr($Event[0]->date_time, 0, 10) }}" disabled>
                                                    <div class="input-group-text"><i class="mdi mdi-calendar"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="formrow-password-input">Duration</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="formrow-password-input" value="{{ $Event[0]->duration }}" disabled>
                                                    <div class="input-group-text"><i class="mdi mdi-clock-time-eight-outline"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="formrow-email-input">Privacy</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="formrow-email-input" value="{{ $Event[0]->privacy }}" disabled>
                                                    <div class="input-group-text"><i class="mdi mdi-lock-outline"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="formrow-password-input">Price</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="formrow-password-input" value="{{ $Event[0]->price }}" disabled>
                                                    <div class="input-group-text"><i class="mdi mdi-currency-usd"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="formrow-email-input">Attendee limit</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="formrow-password-input" value="{{ $Event[0]->attendee_limit }}" disabled>
                                                    <div class="input-group-text"><i class="mdi mdi-chart-line-variant"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="formrow-password-input">Published</label>
                                                <div class="input-group">
                                                    @if ($Event[0]->published == 0)
                                                        <input type="text" class="form-control" id="formrow-password-input" value="False" disabled>
                                                    @else
                                                        <input type="text" class="form-control" id="formrow-password-input" value="True" disabled>
                                                    @endif
                                                    <div class="input-group-text"><i class="mdi mdi-check"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="formrow-email-input">Focused on gender</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="formrow-password-input" value="{{ $Event[0]->focused_on_gender }}" disabled>
                                                    <div class="input-group-text"><i class="mdi mdi-account-multiple-outline"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="formrow-password-input">Focused on age range</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="formrow-password-input" value="{{ $Event[0]->focused_on_age_range }}" disabled>
                                                    <div class="input-group-text"><i class="mdi mdi-account-child-outline"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="formrow-email-input">Promote Event</label>
                                                <div class="input-group">
                                                    @if ($Event[0]->promote_event_id == null)
                                                        <input type="text" class="form-control" id="formrow-password-input" value="False" disabled>
                                                    @else
                                                        <input type="text" class="form-control" id="formrow-password-input" value="True" disabled>
                                                    @endif
                                                    <div class="input-group-text"><i class="mdi mdi-check-box-outline"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="formrow-password-input">Recurrence</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="formrow-password-input" value="{{ $Event[0]->recurrence }}" disabled>
                                                    <div class="input-group-text"><i class="mdi mdi-repeat"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')

@endsection
