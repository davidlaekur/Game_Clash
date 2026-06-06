@extends('layouts.app')

@push('styles')
    @vite('resources/css/intro.scss') <!--solo carga este scss aqui   -->
@endpush

@section('content')
<div class="subspace">
	<div class="hosnian-prime"></div>
	<div class="x-wing">
		<div class="bb-unit"></div>
		<div class="thruster-flicker">
			<div class="thruster t1"></div>
			<div class="thruster t2"></div>
			<div class="thruster t3"></div>
			<div class="thruster t4"></div>
		</div>
	</div>
</div>

<div class="hyperspace">
	<div class="scene">
		<div class="wrap">
			<div class="wall wall-right"></div>
			<div class="wall wall-left"></div>   
			<div class="wall wall-top"></div>
			<div class="wall wall-bottom"></div>
			<div class="wall wall-back"></div>    
		</div>
		<div class="wrap">
			<div class="wall wall-right"></div>
			<div class="wall wall-left"></div>   
			<div class="wall wall-top"></div>
			<div class="wall wall-bottom"></div>
			<div class="wall wall-back"></div>    
		</div>
	</div>
</div>

<div class="space">
	<div class="stars-inner">
		@for ($i = 0; $i < 100; $i++)
			<div class="star"></div>
		@endfor
	</div>
</div>
<script>
    setTimeout(() => {
        window.location.href = "{{ route('adventure.run') }}";
    }, 16000); 
</script>

@endsection
