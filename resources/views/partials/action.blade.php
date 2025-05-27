@if(isset($isEdit) && $isEdit)

<a href="{{ route ($currentRoute.'.edit', $row->id) }}" class="btn btn-info btn-sm btn-action" title="Edit"> <i class="fas fa-pencil-alt"></i> </a>
@endif

@if(isset($isDelete) && $isDelete)
<a data-id="{{ $row->id }}" data-url="{{ route($currentRoute.'.destroy', $row->id) }}"  href="" class="btn btn-danger btn-sm remove-action  btn-action" title="Delete"><i class="fas fa-trash"></i></a>
@endif


@if(isset($isView) && $isView)
<a href="{{ route($currentRoute.'.show', $row->id) }}" class="btn btn-info btn-sm  btn-action" title="View"><span class="fa fa-eye"></span></a>
@endif

@if(isset($isFieldMapping) && $isFieldMapping)
<a href="{{ route ($currentRoute.'.edit', $row->id) }}" class="btn btn-info btn-sm  btn-action" title="Field Mapping"> <i class="fas fa-balance-scale"></i> </a>
@endif

@if(isset($isViewInModel) && $isViewInModel)

<button onclick="showContent({{ $row->id }})" class="btn btn-default btn-action  btn-action" title="View Content"><i class="far fa-eye"></i></button>
@endif

@if(isset($weekUpdate) && $weekUpdate)
<a data-id="{{ $row->id }}" data-url="{{ route($currentRoute.'.week-update', $row->id) }}" href="" class="btn btn-info btn-sm  btn-action week-update" title="Week Update">Unlock Week </a>
@endif
