<x-app-layout>
	{{ Html::style('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css') }}
    {{-- Styles --}}
    {{ Html::style('https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css') }}
    {{ Html::style("designs/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css") }}
    {{ Html::style("designs/plugins/datatables-responsive/css/responsive.bootstrap4.min.css") }}
    {{ Html::style("designs/plugins/datatables-buttons/css/buttons.bootstrap4.min.css") }}

    {{-- Header --}}
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Listing') }}
            </h2>
            <a class="btn btn-info" href="{{ route('listings.create') }}">
                Add Listing
            </a>
        </div>
    </x-slot>

    {{-- Content --}}
    <div class="py-12">
        <div class="card-body">
            <table id="tbl_datatable" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>id</th>
                        <th class="no-sort">Title</th>
                        <th class="no-sort">Description</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- Scripts --}}
@push('js')
    {{-- ✅ Load jQuery first, without integrity attribute --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- ✅ Then Bootstrap --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    {{-- ✅ DataTables & Plugins --}}
    {{ Html::script("designs/plugins/datatables/jquery.dataTables.min.js") }}
    {{ Html::script("designs/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js") }}
    {{ Html::script("designs/plugins/datatables-responsive/js/dataTables.responsive.min.js") }}
    {{ Html::script("designs/plugins/datatables-responsive/js/responsive.bootstrap4.min.js") }}
    {{ Html::script("designs/plugins/datatables-buttons/js/dataTables.buttons.min.js") }}
    {{ Html::script("designs/plugins/datatables-buttons/js/buttons.bootstrap4.min.js") }}
    {{ Html::script("designs/plugins/datatables-buttons/js/buttons.html5.min.js") }}
    {{ Html::script("designs/plugins/datatables-buttons/js/buttons.print.min.js") }}
    {{ Html::script("designs/plugins/datatables-buttons/js/buttons.colVis.min.js") }}

    {{-- SweetAlert2 & others --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.min.js"></script>

    {{-- ✅ Main script --}}
    <script>
        $(document).ready(function () {
            var myTable = $('#tbl_datatable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                searching: true,
                ajax: "{{ route('listings.index') }}",
                deferRender: true,
                lengthMenu: [[25, 50, 100, 150, 200, 500], [25, 50, 100, 150, 200, 500]],
                order: [[0, "ASC"]],
                columns: [
                    { data: 'id', name: 'id', visible: false },
                    { data: 'title', name: 'title' },
                    { data: 'description', name: 'description' },
                    { data: 'price', name: 'price', orderable: false, searchable: false },
                    { data: 'action', orderable: false, searchable: false }
                ],
                drawCallback: function () {
                    $('.toggle-demo').bootstrapToggle();
                },
                createdRow: function (row, data) {
                    $(row).attr('data-id', data.id).addClass('row1');
                },
            });

            $("#tbl_datatable tbody").sortable({
                items: "tr.row1",
                cursor: 'move',
                opacity: 0.6,
                update: function () {
                    sendOrderToServer();
                }
            });

            function sendOrderToServer() {
                var order = [];
                $('tr.row1').each(function (index) {
                    order.push({
                        id: $(this).data('id'),
                        position: index + 1
                    });
                });
                // You can use AJAX here to update sort order
            }

            $('body').on('click', '.remove-action', function (e) {
                e.preventDefault();
                var id = $(this).data('id');
                var url = $(this).data('url');
                swal({
                    title: "Are you sure?",
                    text: "Once deleted, you will not be able to recover this record!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Delete",
                    confirmButtonColor: "#40485b"
                }, function () {
                    $.ajax({
                        type: 'DELETE',
                        url: url,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            if (response.code === 200) {
                                myTable.ajax.reload();
                                swal("Deleted!", response.message, "success");
                            } else {
                                swal("Error!", response.message, "error");
                            }
                        }
                    });
                });
            });
        });
    </script>
@endpush

</x-app-layout>
