@extends('hubspot-integration::layouts.package')

@section('content')
  <div class="row">
    <div class="col p-5">
      {{ $dataTable->table() }}
    </div>
  </div>
@endsection

@push('scripts')
  {{ $dataTable->scripts() }}
@endpush
