@if (isset($properties))
    @foreach ($properties as $property)
        <div class="form-group">
            @if ($property->objectProperty->html_input_type == 'file' and  $property->objectProperty->file_path == 'images/' )
                {!! Form::label($property->key,$property->objectProperty->name) !!}
                {!! Form::file($property->key) !!}
                <img src="{{ $resources_path.$property->value }}" width="200"  alt="Imagen no encontrada ({{$property->objectProperty->name}})" />
            @elseif ($property->objectProperty->html_input_type == 'file' and $property->objectProperty->file_path == 'videos/' )
                {!! Form::label($property->key,$property->objectProperty->name) !!}
                {!! Form::file($property->key) !!}
                <p><a href="{{ $resources_path.$property->value }}">{{$property->key}}</a></p>
            @elseif ($property->objectProperty->html_input_type == 'file' and  $property->objectProperty->file_path == 'fonts/' )
                {!! Form::label($property->key,$property->objectProperty->name) !!}
                {!! Form::file($property->key) !!}
                <p><a href="{{ $resources_path.$property->value }}">{{$property->key}}</a></p>
            @elseif ($property->objectProperty->html_input_type == 'file' and $property->objectProperty->file_path == 'audios/' )
                {!! Form::label($property->key,$property->objectProperty->name) !!}
                {!! Form::file($property->key) !!}
                <p><a href="{{ $resources_path.$property->value }}">{{$property->key}}</a></p>
            @elseif  ($property->objectProperty->html_input_type == 'checkbox' )
                {!! Form::label($property->key,$property->objectProperty->name) !!}
                @if ($property->value == 'false')
                    {!! Form::checkbox($property->key, $property->value,false) !!}
                @else
                    {!! Form::checkbox($property->key, $property->value,true) !!}
                @endif
            @elseif ($property->objectProperty->html_input_type == 'textarea')
                {!! Form::textarea($property->key,$property->value,['class' => 'ckeditor']) !!}}
            @else
                {!! Form::label($property->key,$property->objectProperty->name) !!}
                {!! Form::text($property->key,$property->value, ['class' => 'form-control' ]) !!}
            @endif
        </div>
    @endforeach
@endif