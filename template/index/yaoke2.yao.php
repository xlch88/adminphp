@push('qwq')
1
@endpush
@push('qwq')
2
@endpush
@prepend('qwq')
3243124
@endprepend

@stack('qwq')

@json(['qwq'=>'0w0'])
@php($i = 2)
@switch($i)
    @case(0)
		{{ "i equals 0" }}
        @breakswitch
    @case(1)
        {{ "i equals 1" }}
        @breakswitch
    @case(2)
        {{ "i equals 2" }}
        @breakswitch
    @default
        {{ "i is not equal to 0, 1 or 2" }}
        @breakswitch
@endswitch