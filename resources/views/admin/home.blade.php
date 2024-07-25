@extends('admin.layouts.app')
@section('page', 'Dashboard')
@section('content')

@if (Auth::user()->designation != 3)

<section>
    <div class="row">
        @if($errors->any())
        <div class="alert alert-danger" role="alert">
            {{$errors->first()}}
        </div>
        @endif
        <div class="col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-secondary" onclick="location.href='{{ route('admin.staff.index') }}'" style="cursor: pointer;">
                <div class="card-body">
                    <h4>Staffs <i class="fi fi-br-user"></i></h4>
                    <h2>{{$data->users}}</h2>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-secondary" onclick="location.href='{{ route('admin.store.index') }}'" style="cursor: pointer;">
                <div class="card-body">
                    <h4>Customers <i class="fi fi-br-user"></i></h4>
                    <h2>{{$data->stores}}</h2>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-secondary" onclick="location.href='{{ route('admin.user.index', 'supplier') }}'" style="cursor: pointer;">
                <div class="card-body">
                    <h4>Suppliers <i class="fi fi-br-user"></i></h4>
                    <h2>{{$data->suppliers}}</h2>
                </div>
            </div>
        </div>        
        <div class="col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-secondary" onclick="location.href='{{ route('admin.product.index') }}'" style="cursor: pointer;">
                <div class="card-body">
                    <h4>Products <i class="fi fi-br-cube"></i></h4>
                    <h2>{{$data->products}}</h2>
                </div>
            </div>
        </div>
    </div>
</section>
<section>
    <div class="row">        
        <div class="col-xl-6">
            <canvas id="myChart" style="width:100%;max-width:600px"></canvas>
        </div>
        <div class="col-xl-6">
            <canvas id="myChartDuePayment" style="width:100%;max-width:600px"></canvas>
        </div>  
    </div>
</section>

@endif

@php
    $sales_store_names = array();
    $sales_store_amount = array();
    foreach($data->store_sales_data as $s){
        array_push($sales_store_names,!empty($s->bussiness_name)?$s->bussiness_name:$s->store_name);
        array_push($sales_store_amount,$s->amount);
    }

    $due_store_names = array();
    $due_store_amount = array();
    
    $last_unpaid_stores = $data->last_unpaid_stores;
    foreach($last_unpaid_stores as $item){
        array_push($due_store_names, $item['store_name']);
        array_push($due_store_amount, replaceMinusSign($item['amount']));
    }


    

@endphp
@endsection
@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
<script>
    var xValues = [];
    var yValues = [];
    xValues = <?php echo json_encode($sales_store_names); ?>;
    console.log("sales_store_names>>",xValues);
    yValues = <?php echo json_encode($sales_store_amount); ?>;
    console.log("sales_store_names>>",yValues);
    var barColors = ["red", "green","blue","orange","brown"];


    new Chart("myChart", {
        type: "bar",
        data: {
            labels: xValues,
            datasets: [{
            // backgroundColor: barColors,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1,
            data: yValues
            }]
        },
        options: {
            legend: {display: false},
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            title: {
                display: true,
                text: "Store Wise Sales"
            }
        }
    });

    

    var xValuesDue = [];
    var yValuesDue = [];
    xValuesDue = <?php echo json_encode($due_store_names); ?>;
    console.log("due_store_names>>",xValuesDue);
    yValuesDue = <?php echo json_encode($due_store_amount); ?>;
    console.log("dues2>>",yValuesDue);
    var barColors = ["red", "green","blue","orange","brown"];

    new Chart("myChartDuePayment", {
        type: "bar",
        data: {
            labels: xValuesDue,
            datasets: [{
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1,
            data: yValuesDue
            }]
        },
        options: {
            legend: {display: false},
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            title: {
                display: true,
                text: "Due Payments"
            }
        }
    });
</script>
@endsection

