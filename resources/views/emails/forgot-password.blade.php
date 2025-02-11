@extends('emails.layout')
@section('content')
    <div>
        <div style="background-color: #66bb6a;padding: 10px;display: flex;gap: 1rem;align-items: center;">
            <h3  style="margin: 0; color: white;">SUNN CONNECTS</h3>
        </div>
        <div style="padding: 20px;">
            <p style="margin-top: 0;">Hello {{$data['first_name']}}, you have forgotten your password. Here is a temporary one:</p>
            <div style="padding: 15px; font-size: 18pt; background-color: lightgray; width: auto; display: inline-block; border-radius: 3px;">
                {{ $data['password'] }}
            </div>
            <p style="margin-bottom: 0;">
                <small><i>Note: Do not share password, one-time-pin, birthday or any sensitive information.</i></small>
            </p>
        </div>
    </div>
@endsection