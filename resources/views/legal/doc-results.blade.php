@php
$i = 1;
@endphp

@if(empty($files->file_path) && count($matchingAttachments) == 0)
<tr>
        <td colspan="100%">
            <h6 class="no-documents-found" style="text-align: center;">No Documents Found</h6>
        </td>
    </tr>
@endif

@php
$i = 1;
if(!empty($files->file_path))
{
    $files = explode(',',$files->file_path);    
}
else
{
    $files =[];
}

@endphp
@if($files)

    @foreach($files as $key => $file)
        @if(stripos($file, $query) !== false)
            <tr>
                <td>{{$i++}}</td>   
                <td>{{date('d-m-Y', strtotime($files->created_at))}}</td>   
                <td>{{$file}}</td>
                <td><a href="{{url('uploads/legal/'.$file)}}" target="_blank"><i data-feather='download'></i></a></td>
            </tr>
        @endif
    @endforeach
@endif

@foreach($matchingAttachments as $attach)
    <tr>
        <td>{{$i++}}</td>   
        <td>{{date('d-m-Y', strtotime($attach->created_at))}}</td>   
        <td>{{$attach->file_name}}</td>
        <td><a href="{{url('attachments/'.$attach->file_path)}}" target="_blank"><i data-feather='download'></i></a></td>
    </tr>
@endforeach

