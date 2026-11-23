@extends('layout.master')
@section('title', $title)

@section('main-section')
    <x-alert-modal :type="session('success') ? 'success' : (session('error') ? 'error' : '')" :message="session('success') ?? session('error')" />

    <div class="page-wrapper">
        <div class="content">

            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">{{ $title }}</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item" aria-current="page">{{ $titleRoute }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="mb-2">
                        <a href="{{ route('masters.organisation.designation.create') }}"
                            class="btn btn-primary d-flex align-items-center">
                            <i class="ti ti-circle-plus me-2"></i> Add Designation
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive mt-3">
                                <table id="designationList" class="display table table-striped table-bordered nowrap">
                                    <thead>
                                        <tr>
                                            <th>Sn</th>
                                            <th>Company Name</th>
                                            <th>Branch Name</th>
                                            <th>Category Name</th>
                                            <th>Department Name</th>
                                            <th>Designation Name</th>
                                            <th>Code</th>
                                            <th>KPI</th>
                                            <th>Compentency</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <x-footer />
    </div>

    <div class="modal fade" id="delete_designation_modal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <span class="avatar avatar-xl bg-transparent-danger text-danger mb-3">
                        <i class="ti ti-trash-x fs-36"></i>
                    </span>
                    <h4 class="mb-1">Confirm Delete</h4>
                    <p class="mb-3">Are you sure you want to delete this designation?</p>
                    <input type="hidden" id="deleteDesignationId">
                    <div class="d-flex justify-content-center">
                        <button class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-danger" id="confirmDeleteDesignation">Yes, Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after_scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script>
        $(document).ready(function() {
            $('#designationList').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                scrollX: true,
                order: [
                    [0, 'asc']
                ],
                ajax: "{{ route('masters.organisation.designation.list') }}",
                columns: [{
                        data: 'DT_RowIndex'
                    },
                    {
                        data: 'company_name'
                    },
                    {
                        data: 'branch_name'
                    },
                    {
                        data: 'category_name'
                    },
                    {
                        data: 'department_name'
                    },
                    {
                        data: 'designation_name'
                    },
                    {
                        data: 'designation_code'
                    },
                    {
                        data: 'kpi_weightage'
                    },
                    {
                        data: 'competency_weight'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data) {
                            return `
                                <div class="action-icon d-inline-flex">
                                    <a href="{{ url('master/designation/edit') }}/${data}" class="me-2" title="Edit">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    <a href="javascript:void(0);" onclick="deleteDesignation(${data})" title="Delete">
                                        <i class="ti ti-trash"></i>
                                    </a>
                                </div>`;
                        }
                    }
                ]
            });
        });

        function deleteDesignation(id) {
            $('#deleteDesignationId').val(id);
            $('#delete_designation_modal').modal('show');
        }

        $('#confirmDeleteDesignation').click(function() {
            var id = $('#deleteDesignationId').val();
            $.ajax({
                url: "{{ url('master/designation/delete') }}/" + id,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: "{{ csrf_token() }}"
                },
                success: function() {
                    $('#delete_designation_modal').modal('hide');
                    $('#designationList').DataTable().ajax.reload(null, false);
                },
                error: function(err) {
                    $('#delete_designation_modal').modal('hide');
                    alert(err.responseJSON?.message || 'Something went wrong');
                }
            });
        });
    </script>
@endpush
