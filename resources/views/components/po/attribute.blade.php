@foreach ($row->attributes as $attribute)
    <span class='badge rounded-pill badge-light-primary'><strong>{{$attribute?->headerAttribute?->name}}</strong>: {{$attribute?->headerAttributeValue?->value}}</span>
@endforeach