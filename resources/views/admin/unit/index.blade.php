@extends('admin.layouts.app')
@section('page', 'Unit')
@section('content')
<section>
    <div class="row">
        <div class="col-sm-8">  
            <div class="search__filter">
                <div class="row align-items-center justify-content-between">
                    <div class="col">
                        
                    </div> 
                    <div class="col-auto">
                        <form method="GET" action="{{ route('admin.unit.index') }}" id="searchForm">
                        <div class="row g-3 align-items-center">
                            <div class="col-auto">
                                <input type="search" name="term" value="{{$term}}" class="form-control" placeholder="Search here..">
                            </div>
                            <input type="submit" hidden />
                        </div>
                        </form>
                    </div>                           
                </div>
            </div>
            <form action="{{ route('admin.unit.bulkSuspend') }}" method="post">
                @csrf
                <div class="filter">
                    <div class="row align-items-center justify-content-between">
                        <div class="col">
                            <div class="row g-3 align-items-center">
                                
                                <div class="col-auto">
                                    <button id="btnSuspend" type="submit" class="btn btn-outline-danger btn-sm">Suspend</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            
                            <p>{{$total}} Total Items</p>
                        </div>
                    </div>
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th class="check-column">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="checkAll">
                                    <label class="form-check-label" for=""></label>
                                </div>
                            </th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Published Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $index => $item)

                        @php
                        if (!empty($_GET['status'])) {
                            if ($_GET['status'] == 'active') {
                                if ($item->status == 0) continue;
                            } else {
                                if ($item->status == 1) continue;
                            }
                        }
                        @endphp


                        {{-- @forelse ($data as $index => $item) --}}
                        <tr>
                            <td class="check-column">
                                <input name="suspend_check[]" class="data-check" type="checkbox"  value="{{$item->id}}">
                            </td>                                    
                            <td>
                            {{$item->name}}
                            <div class="row__action">
                                <a href="{{ route('admin.unit.view', $item->id) }}">Edit</a>
                                <a href="{{ route('admin.unit.view', $item->id) }}">View</a>
                                <a href="{{ route('admin.unit.status', $item->id) }}">{{($item->status == 1) ? 'Suspend' : 'Active'}}</a>                                      
                            </div>
                            </td>
                            <td>{{ $item->description }}</td>
                            <td>{{date('d/m/Y', strtotime($item->created_at))}}</td>
                            <td><span class="badge bg-{{($item->status == 1) ? 'success' : 'danger'}}">{{($item->status == 1) ? 'Active' : 'Suspend'}}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="100%" class="small text-muted">No data found</td></tr>
                        @endforelse
                    </tbody>
                </table> 

                {{$data->links()}}
            </form> 
        </div>
        <div class="col-sm-4">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.unit.store') }}" >
                    @csrf
                        <h4 class="page__subtitle">Add New</h4>
                        @if($errors->any())                            
                                                       
                            {!! implode('', $errors->all('<p class="small text-danger">:message</p>')) !!}
                        @endif
                        
                        <div class="form-group mb-3">
                            <label class="label-control">Name <span class="text-danger">*</span> </label>
                            <input type="text" name="name" placeholder="" class="form-control" value="{{old('name')}}">
                            @error('name') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="label-control">Description </label>
                            <textarea name="description" class="form-control">{{old('description')}}</textarea>
                            @error('description') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-sm btn-success">Add New</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<script>

    $(document).ready(function(){
        $('#btnSuspend').prop('disabled', true);
        
        $("#checkAll").change(function () {
            $("input:checkbox").prop('checked', $(this).prop("checked"));
            var checkAllStatus = $("#checkAll:checked").length;
            // console.log(checkAllStatus)
            if(checkAllStatus == 1){
                $('#btnSuspend').prop('disabled', false);
            }else{
                $('#btnSuspend').prop('disabled', true);
            }
        });

        $('.data-check').change(function () {
            $('#btnSuspend').prop('disabled', false);
            var total_checkbox = $('input:checkbox.data-check').length;
            var total_checked = $('input:checkbox.data-check:checked').length;
            // console.log( total_checkbox);
            // console.log(total_checked);

            if(total_checked == 0){
                $('#btnSuspend').prop('disabled', true);
            }
          
            if(total_checkbox == total_checked){
                console.log('All checked')
                $('#checkAll').prop('checked', true);
            }else{
                console.log('Not All checked')
                $('#checkAll').prop('checked', false);
            }
        })
    });
    $('input[type=search]').on('search', function () {
        // search logic here
        // this function will be executed on click of X (clear button)
        $('#searchForm').submit();
    });
    
</script>
@endsection

