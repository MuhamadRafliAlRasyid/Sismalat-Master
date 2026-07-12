@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Dashboard Admin</h1>
    <p>Selamat datang, {{ Auth::user()->name }}. Ini adalah dashboard Spv.</p>
@endsection
