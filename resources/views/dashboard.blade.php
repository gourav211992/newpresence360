@extends('layouts.app')

@section('content')
<div class="app-content content ">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
         
        <div class="content-body">
              
            
            <section id="basic-datatable">
                <div class="row">
                    <div class="col-12">  
                        
                        <div class="overflow-hidden card">
                             <div class=""> 
                                     <img src="{{ url('/assets/css/dashboard.svg') }}" class="w-100" />
                                 
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Modal to add new record -->
                 
            </section>
             

        </div>
    </div>
</div>
@endsection
@section('scripts')

@endsection