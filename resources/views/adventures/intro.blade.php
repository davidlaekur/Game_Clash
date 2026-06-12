@extends('layouts.app')

@section('music', 'aventura')

@push('styles')
    @vite('resources/css/intro.scss') <!--solo carga este scss aqui   -->
@endpush

@section('content')

{{-- rótulo de marca: aparece arriba al inicio y se desvanece antes del viaje --}}
<div class="intro-brand">
	<span class="intro-brand__crest"><i class="fas fa-jedi" aria-hidden="true"></i></span>
	<h1 class="intro-brand__title">World of <span>Laraveland</span></h1>
	<p class="intro-brand__sub">Crónicas de la Aventura</p>
</div>

{{-- texto de historia con scroll en perspectiva (estilo Star Wars) --}}
<div class="intro-crawl">
	<div class="intro-crawl__content">
		<p class="intro-crawl__episode">Crónicas de Laraveland</p>
		<h2 class="intro-crawl__title">La Llamada de la Aventura</h2>
		<p>El Reino de Laraveland vive tiempos convulsos. Las facciones de los
		Guardianes de Laraveland y la Legión de Ítaca se disputan cada territorio
		del mapa, y la frontera arde.</p>
		<p>Un nuevo héroe se alza entre las estrellas. A bordo de su nave, parte
		de su mundo natal rumbo a tierras lejanas, donde antiguos enigmas
		aguardan a quien ose responderlos.</p>
		<p>Más allá del salto a la velocidad de la luz, un planeta desconocido
		esconde las pruebas que forjarán tu leyenda. Que la fortuna guíe tu
		viaje, aventurero...</p>
	</div>
</div>

{{-- campo de estrellas profundo (paralaje) --}}
<div class="space">
	<div class="stars-inner">
		@for ($i = 0; $i < 100; $i++)
			<div class="star"></div>
		@endfor
	</div>
	{{-- planetas de fondo que asoman en la travesía --}}
	<div class="far-planets">
		<div class="far-planet far-planet--blue"></div>
		<div class="far-planet far-planet--ringed"></div>
		<div class="far-planet far-planet--red"></div>
	</div>
</div>

{{-- salto a la velocidad de la luz (nuestra versión: líneas radiales) --}}
<div class="lightspeed">
	<div class="lightspeed__core"></div>
	@for ($i = 0; $i < 180; $i++)<span class="streak"></span>@endfor
	<div class="lightspeed__flash"></div>
</div>

{{-- planeta destino: obra maestra procedural (SVG feTurbulence) --}}
<div class="planet">
	<svg class="planet__svg" viewBox="0 0 220 220" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
		<defs>
			<linearGradient id="planetBands" x1="0" y1="0" x2="0.14" y2="1">
				<stop offset="0"    stop-color="#244f86"/>
				<stop offset="0.08" stop-color="#3f86c8"/>
				<stop offset="0.15" stop-color="#73b8ec"/>
				<stop offset="0.23" stop-color="#2c63a6"/>
				<stop offset="0.33" stop-color="#9fd6f5"/>
				<stop offset="0.42" stop-color="#356fb0"/>
				<stop offset="0.52" stop-color="#bfe6ff"/>
				<stop offset="0.60" stop-color="#2a5f9e"/>
				<stop offset="0.70" stop-color="#6fb6ec"/>
				<stop offset="0.80" stop-color="#31649f"/>
				<stop offset="0.90" stop-color="#8ccaf2"/>
				<stop offset="1"    stop-color="#21487c"/>
			</linearGradient>

			<radialGradient id="planetStorm" cx="50%" cy="50%" r="50%">
				<stop offset="0"   stop-color="#e8f5ff"/>
				<stop offset="0.4" stop-color="#a9d6f5"/>
				<stop offset="0.8" stop-color="#5a93cc"/>
				<stop offset="1"   stop-color="rgba(90,147,204,0)"/>
			</radialGradient>

			<!-- iluminación de esfera: brillo arriba-izq, terminador abajo-der -->
			<radialGradient id="planetShade" cx="36%" cy="30%" r="78%">
				<stop offset="0"    stop-color="rgba(255,255,255,.45)"/>
				<stop offset="0.34" stop-color="rgba(255,255,255,0)"/>
				<stop offset="0.70" stop-color="rgba(4,12,32,.32)"/>
				<stop offset="1"    stop-color="rgba(0,3,14,.92)"/>
			</radialGradient>

			<!-- halo / limbo atmosférico -->
			<radialGradient id="planetAtmo" cx="50%" cy="50%" r="50%">
				<stop offset="0.80" stop-color="rgba(150,210,255,0)"/>
				<stop offset="0.93" stop-color="rgba(150,210,255,.3)"/>
				<stop offset="0.985" stop-color="rgba(205,235,255,.65)"/>
				<stop offset="1"    stop-color="rgba(150,210,255,0)"/>
			</radialGradient>

			<!-- textura procedural: deforma las bandas con ruido fractal -->
			<filter id="planetWarp" x="-20%" y="-20%" width="140%" height="140%">
				<feTurbulence type="fractalNoise" baseFrequency="0.018 0.055" numOctaves="4" seed="11" result="noise"/>
				<feDisplacementMap in="SourceGraphic" in2="noise" scale="20" xChannelSelector="R" yChannelSelector="G"/>
			</filter>

			<clipPath id="planetClip"><circle cx="110" cy="110" r="92"/></clipPath>
		</defs>

		<!-- superficie: bandas + tormenta, deformadas y en rotación -->
		<g clip-path="url(#planetClip)">
			<g filter="url(#planetWarp)">
				<g class="planet__spin">
					<rect x="-110" y="16" width="440" height="188" fill="url(#planetBands)"/>
					<ellipse cx="60"  cy="128" rx="30" ry="15" fill="url(#planetStorm)" opacity=".9"/>
					<ellipse cx="250" cy="128" rx="30" ry="15" fill="url(#planetStorm)" opacity=".9"/>
					<ellipse cx="150" cy="86"  rx="18" ry="9"  fill="url(#planetStorm)" opacity=".7"/>
				</g>
			</g>
		</g>

		<!-- sombreado 3D y atmósfera -->
		<circle cx="110" cy="110" r="92" fill="url(#planetShade)"/>
		<circle cx="110" cy="110" r="95" fill="url(#planetAtmo)"/>
	</svg>
</div>

{{-- compañía del destino: lunas/planetas que vemos al acercarnos --}}
<div class="approach">
	<div class="approach-planet approach-planet--moon"></div>
	<div class="approach-planet approach-planet--far"></div>
	<div class="approach-planet approach-planet--small"></div>
</div>

{{-- efecto de llegada: pequeña explosión al alcanzar el planeta --}}
<div class="arrival">
	<div class="arrival__flash"></div>
	<div class="arrival__ring"></div>
	<div class="arrival__ring arrival__ring--2"></div>
	<div class="arrival__sparks">
		@for ($i = 0; $i < 12; $i++)<span class="arrival__spark"></span>@endfor
	</div>
</div>

{{-- la nave (de CodePen: x-wing + BB-8) --}}
<div class="subspace">
	<div class="x-wing">
		<div class="boost-flame"></div>
		<div class="bb-unit"></div>
		<div class="thruster-flicker">
			<div class="thruster t1"></div>
			<div class="thruster t2"></div>
			<div class="thruster t3"></div>
			<div class="thruster t4"></div>
		</div>
	</div>
</div>

<script>
    setTimeout(() => {
        window.location.href = "{{ route('adventure.run') }}";
    }, 21000);
</script>

@endsection
