@extends('layouts.master')

@section('title') @lang('translation.Users') @endsection

@section('content')

    @component('common-components.breadcrumb')
        @slot('pagetitle') @lang('translation.Users') @endslot
        @slot('title') @lang('translation.Detail') @endslot
    @endcomponent


    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title col-sm-4">
                        @lang('translation.Detail'): {{ $User[0]->name }}
                    </h4>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 offset-md-3">
                            <div class="mt-4">
                                <form>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="formrow-firstname-input">Name</label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" id="formrow-firstname-input" value="{{ $User[0]->name }}" disabled>
                                                            <div class="input-group-text"><i class="mdi mdi-account-circle-outline"></i></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="formrow-firstname-input">About</label>
                                                        <textarea class="form-control" id="formrow-firstname-input" disabled>{{ $User[0]->user_profile->about_me }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label" for="formrow-password-input">Photo</label>
                                                <img src="{{ $User[0]->user_photos[0]->path }}" alt="UserPhoto" srcset="" style="object-fit: cover; width: 180px; height: 180px;">
                                            </div>
                                        </div>
                                    </div>



                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="formrow-email-input">Email</label>
                                                <div class="input-group">
                                                    <input type="email" class="form-control" id="formrow-email-input"  value="{{ $User[0]->email }}" disabled>
                                                    <div class="input-group-text"><i class="mdi mdi-email-outline"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="formrow-password-input">Phone number</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="formrow-password-input" value="{{ $User[0]->phone_number }}" disabled>
                                                    <div class="input-group-text"><i class="mdi mdi-phone-dial-outline"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="formrow-email-input">Birthday</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="formrow-email-input"  value="{{ Str::substr($User[0]->birthday_at, 0, 10) }}" disabled>
                                                    <div class="input-group-text"><i class="mdi mdi-calendar"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="formrow-password-input">Gender</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="formrow-password-input" value="{{ $User[0]->gender }}" disabled>
                                                    <div class="input-group-text"><i class="mdi mdi-account-multiple-outline"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="formrow-email-input">Lives In</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="formrow-password-input" value="{{ $User[0]->user_profile->lives_in }}" disabled>
                                                    <div class="input-group-text"><i class="mdi mdi-home-outline"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="formrow-password-input">From</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="formrow-password-input" value="{{ $User[0]->user_profile->from }}" disabled>
                                                    <div class="input-group-text"><i class="mdi mdi-map-marker-radius-outline"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="formrow-email-input">Work</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="formrow-password-input" value="{{ $User[0]->user_profile->work }}" disabled>
                                                    <div class="input-group-text"><i class="mdi mdi-folder-settings-outline"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="formrow-password-input">Education</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="formrow-password-input" value="{{ $User[0]->user_profile->education }}" disabled>
                                                    <div class="input-group-text"><i class="mdi mdi-book-edit-outline"></i></div>
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
