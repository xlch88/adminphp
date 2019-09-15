<pre>
{{-- ohhhhhhhhhhhhhhhhhhhhhhh! --}}
@php($qwq = 1)
{{ $qwq }}
{!! $qwq !!}
{!! $qwq or $fuck !!}

@if(1 == TRUE)
	qwq
@elseif(2 == true)
	0w0
@else
	哇哇哇
@endif

@foreach(['a', 'b', 'c'] as $qwq)
	{{ $qwq }}
@endforeach

@for($x = 0; $x < 10; $x++)
	@continue
	@continue(1)
	@continue($a > 233)
	@break
	@break(1)
	@break($a > 233)
@endfor

@isset($a)
	0w0
	@empty($a)
		qwq
	@endempty
	
	@unless($a)
		wawa
	@endunless
@endisset

@php
	echo 'hello world';
@endphp

@php(echo 'hello world')

{{-- @qwq('wawawa') --}}
1
@iftest(qwq)
2
@endiftest
</pre>
<pre>@php(echo htmlspecialchars_decode('&lt;hr/&gt;') . file_get_contents(templatePath . 'index/yaoke.yao.php');)</pre>
@php(echo htmlspecialchars_decode('&lt;hr/&gt;'); highlight_string(file_get_contents(__FILE__));)