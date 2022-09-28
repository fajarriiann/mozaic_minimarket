@extends('adminlte::page')

@section('title', 'MOZAIC Minimarket')
@section('js')
<script>
  // $(document).ready(function(){
  //   var check_data = {!! session('check_data') !!};
  //   if (check_data == 1) {
  //     alert('asa');
  //   }
  // });

  function functin_click_btn(){
    $.ajax({
				type: "GET",
				url : "{{route('check-data-configuration')}}",
				success: function(data){
          // console.log(data);
          if (data != '[null]') {
            $('#modal').modal('show');
          } else {
            $.ajax({
                type: "GET",
                url : "{{route('configuration-data-dwonload')}}"
              });
              location.reload();
          }
			}
		});
  }
</script>
@endsection
@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
      <li class="breadcrumb-item active" aria-current="page">Konfigurasi Data</li>
    </ol>
  </nav>

@stop

@section('content')

<h3 class="page-title">
  <b>Konfigurasi Data</b>
</h3>
<br/>
@if(session('msg'))
<div class="alert alert-info" role="alert">
    {{session('msg')}}
</div>
<br/>
@endif 

<div class="modal fade" id="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title" id="staticBackdropLabel">Data Stock Ada yang Berbeda</h5>
      </div>
      <div class="modal-body">
        Apakah anda ingin mengganti data yang sudah ada?
      </div>
      <div class="modal-footer">
        <a href="{{ route('configuration-data-dwonload') }}" class="btn btn-success">Iya</a>
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tidak</button>
      </div>
    </div>
  </div>
</div>

<div style="display: flex; justify-content: center; align-items: center; height: 200px; ">
  <a onclick="functin_click_btn()" class="btn btn-success mr-3 btn-lg"><i class="fa fa-download"></i> Unduh Data</a>
  <a href="{{ route('configuration-data-upload') }}" class="btn btn-success btn-lg"><i class="fa fa-upload"></i> Unggah Data</a>
</div>

@stop

@section('footer')
    
@stop

@section('css')
    
@stop

@section('js')
    
@stop