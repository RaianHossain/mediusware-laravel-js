@extends('layouts.app')

@section('content')

<!-- <div>
    <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault();
                                                 document.getElementById('logout-form').submit();">
                                {{ __('Logout') }}
                            </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
        <button type="submit">Logout</button>
    </form>
</div> -->

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800" style="color: #666666; font-size: 36px;">Products</h1>
</div>



    @if(session()->has('message'))
        <div class="alert alert-success">
            {{ session()->get('message') }}
        </div>
    @endif


    <div class="card">
        <form action="{{ route('product.index') }}" method="get" class="card-header">
            <div class="form-row justify-content-between">
                <div class="col-md-2">
                    <input type="text" name="title" placeholder="Product Title" class="form-control" value="{{ isset($data) ? $data['title'] : '' }}">
                </div>
                
                <div class="col-md-2">
                    <select name="variant" id="Variant" class="form-control" >
                        <option value=""></option>
                        <option value="">...Select A Variant</option>
                        @foreach($variantsArray as $name => $varss)
                        <optgroup label="{{ $name }}">
                            @foreach($varss as $var)
                            <option value="{{ $var }}" @if(isset($data)) {{ $data['variant'] == $var ? 'selected' : '' }} @endif>{{ $var }}</option>
                            @endforeach
                        </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Price Range</span>
                        </div>
                        <input type="text" name="price_from" aria-label="First name" placeholder="From" class="form-control" value="{{ isset($data) ? $data['price_from'] : '' }}">
                        <input type="text" name="price_to" aria-label="Last name" placeholder="To" class="form-control" value="{{ isset($data) ? $data['price_to'] : '' }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date" placeholder="Date" class="form-control" value="{{ isset($data) ? $data['date'] : '' }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary float-right" style="color: black"><i class="fa fa-search"></i>Search</button>
                </div>
            </div>
        </form>

        <div class="card-body">
            <div class="table-response">
                <table class="table">
                    <thead  style="color: #666666; font-weight: bold">
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Variant</th>
                        <th width="150px">Action</th>
                    </tr>
                    </thead>

                    <tbody style="color: #666666;">
                    @php $index = $startingFrom ?? 1; @endphp
                    @forelse($products as $product)
                    <tr>
                        <td>{{ $index++ }}</td>
                        <td style="width: 15%">{{ $product->title }} <br> Created at : {{ $product->created_at->format('d-M-Y') }}</td>
                        <td style="width:30%" data-toggle="tooltip" data-placement="top" title="{{ $product->description }}">{{ \Illuminate\Support\Str::limit($product->description, 100, $end='...') }}</td>
                        <td>
                            <dl class="row mb-0" style="height: 80px; overflow: hidden" id="variant-{{ $product->id }}">
                                @forelse($product->product_variant_prices as $product_variant_with_price)
                                <dt class="col-sm-3 pb-0 pb-1">
                                    {{ $product_variant_with_price->product_variant_two_details ? strtoupper($product_variant_with_price->product_variant_two_details->variant) : '' }}
                                    {{ $product_variant_with_price->product_variant_two_details && $product_variant_with_price->product_variant_one_details ? '/ ' : '' }}
                                    {{ $product_variant_with_price->product_variant_one_details ? ucfirst($product_variant_with_price->product_variant_one_details->variant) : '' }} 
                                    {{ $product_variant_with_price->product_variant_one_details && $product_variant_with_price->product_variant_three_details ? '/ ' : '' }}
                                    {{ $product_variant_with_price->product_variant_three_details ? $product_variant_with_price->product_variant_three_details->variant : '' }}
                                </dt>
                                <dd class="col-sm-9">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4 pb-0">Price : {{ number_format($product_variant_with_price->price, 2) }}</dt>
                                        <dd class="col-sm-8 pb-0">InStock : {{ number_format($product_variant_with_price->stock, 2) }}</dd>
                                    </dl>
                                </dd>
                                @empty
                                @endforelse
                            </dl>
                            <button onclick="$('#variant-{{ $product->id }}').toggleClass('h-auto')" class="btn btn-sm btn-link">Show more</button>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('product.edit', $product) }}" class="btn btn-success">Edit</a>
                            </div>
                        </td>
                    </tr>
                    @empty

                    @endforelse
                    </tbody>

                </table>
            </div>

        </div>

        <div class="card-footer">
            <div class="row justify-content-between">
                <div class="col-md-12">
                    @if($totalProducts > 0)
                    <div class="d-flex justify-content-between">
                        <p>Showing {{ $startingFrom ?? 0 }} to {{ $endingAt ?? $products->count() }} out of {{ $totalProducts ?? $products->count() }}</p>
                        @if($noReq)
                        <div>
                            {{ $products->links() }}
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
                <div class="col-md-2">

                </div>
            </div>
        </div>
    </div>

@endsection
