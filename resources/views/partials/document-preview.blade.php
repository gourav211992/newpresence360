<div class = "col-md-6" style = "margin-top:19px;">
    <div class = "row" id = "{{$elementKey}}">
    @if($documents && $documents->count())
            @foreach($documents as $key => $attachment)
            <div class="col-md-1 file-upload-preview" style="cursor: pointer;">
                <div class="image-uplodasection expenseadd-sign">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text" onclick="previewFile(this);" file-url="{{$attachment->getDocumentUrl($attachment)}}"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    @if($document_status == \App\Helpers\ConstantHelper::DRAFT || intval(request('amendment')))
                    <div class="delete-img text-danger" data-edit-flag="true" data-index="{{$key+1}}" data-id="{{$attachment->id}}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
    @endif
    </div>
</div> 