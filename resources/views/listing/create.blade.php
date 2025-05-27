@extends('admin.layouts.layout')
@section('title', $page_title .' ' . $module)
@section('content')
<!-- Content Header (Page header) -->
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
			<div>
				<h1>{{$module}}</h1>
			</div> 
		</div>
		<div class="page-title-actions d-inline-block ">
			<div class=" float-right">
			{{ Breadcrumbs::render("tool".$page_title) }}
			</div>
		</div><!-- /.col -->
	</div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->
<section class="content">
	<div class="container-fluid">
		@include('admin.layouts.alert_message')
		<div class="row">
			<!-- left column -->
			<div class="col-md-12">
				<!-- general form elements -->
				<div class="card card-info">

					{{-- <div class="card-header">
						<h3 class="card-title">{{ $page_title }}</h3>
					</div> --}}
					<!-- /.card-header -->
					{{ Form::open(array('url' => route($action_url, $action_params), 'method'=> $method, 'enctype' => 'multipart/form-data', 'class' => 'form-vertical',
						'name' => 'tool_form')) }}
                    @if ($method === "PUT")
                    <input type="hidden" id="id" name="id" value="{{ $action_params }}">
                    @endif
					<!-- form start -->
					<div class="card-body">
						<div class="row">
							@php
							$titleRequired = $korean_titleRequired = 1;
							$descriptionRequired=0
                            @endphp
							@include('admin.input.title')
							@include('admin.input.k_title')
						</div>
						<div class="row">
							<div class="col-12 col-sm-6">
                                <div class="form-group">
                                    <label for="file">Screen Image<span class="error">*</span></label>
                                    <input type="file" name="screen_image" class="form-control" id="screen_image" accept="image/*" />
                                    <!-- Error -->
                                    @if ($errors->first('screen_image'))
                                    <div class="error">
                                        {{ $errors->first('screen_image') }}
                                    </div>
                                    @endif
									@isset($formObj->screen_image)
                                        <img src="{{$screen_image}}" width="150px" height="150px" class="img-thumbnail" alt="image">
                                    @endisset
                                </div>
                            </div>
						</div>
						<hr>
						<span class="mb-2"><b>Tool Information </b></span>
						<div class="row">
							<div class="col-12 col-sm-6">
								<div class="form-group">
									<label for="information">Information</label>
									{{ Form::textarea('information', (old('information')) ? old('information') : $formObj->information, ['class' => 'form-control', 'placeholder' => 'Information', 'id' => 'information']) }}
									<!-- Error -->
									@if ($errors->has('information'))
									<div class="error">
										{{ $errors->first('information') }}
									</div>
									@endif
								</div>
							</div>
							<div class="col-12 col-sm-6">
								<div class="form-group">
									<label for="k_information">정보 제목</label>
									{{ Form::textarea('k_information',(old('k_information'))?old('k_information'):$formObj->k_information, ['class' => 'form-control', 'placeholder' => '정보', 'id' => 'k_information']) }}
									<!-- Error -->
									@if ($errors->has('k_information'))
									<div class="error">
										{{ $errors->first('k_information') }}
									</div>
									@endif
								</div>
							</div>
						</div>
						<hr>
						<span class="mb-2"><b>Tool Media(It's for Guided Imagery & Progressive Muscle Relaxation Tool)</b></span>
						<div class="row pt-4">
							<div class="col-12 col-sm-6">
								<div class="form-group">
									<label for="tool_audio">Audio<span class="error">*</span></label>
									<input type="file" name="tool_audio" class="form-control" id="tool_audio" accept="audio/*">
									<!-- Error -->
									@if ($errors->first('tool_audio'))
										<div class="error">
											{{ $errors->first('tool_audio') }}
										</div>
									@endif
									<div class="imgPreview">
                                        @isset($tool_audio)
                                            <audio controls>
                                                <source type="audio/mp3" src="{{$tool_audio}}">
                                            </audio>
                                        @endisset
								    </div>
								</div>
							</div>
							<div class="col-12 col-sm-6">
								<div class="form-group">
									<label for="k_tool_audio">오디오<span class="error">*</span></label>
									<input type="file" name="k_tool_audio" class="form-control" id="k_tool_audio" accept="audio/*">
									<!-- Error -->
									@if ($errors->first('k_tool_audio'))
									<div class="error">
										{{ $errors->first('k_tool_audio') }}
									</div>
									@endif
									<div class="imgPreview">
                                        @isset($k_tool_audio)
                                            <audio controls>
                                                <source type="audio/mp3" src="{{$k_tool_audio}}">
                                            </audio>
                                        @endisset
								    </div>
								</div>
							</div>
							<div class="col-12 col-sm-6">
                                <div class="form-group">
                                    <label for="image_type">Image Type <span class="error">*</span></label>
                                    <div class="select-box">
										@if($selectedImageType != null)
											{!! Form::hidden('image_type', $selectedImageType) !!}
											{!! Form::select('image_type_select', ["" => 'Select Image Type'] + $image_type, $selectedImageType, [
												'class' => 'form-control',
												'id' => 'image_type',
												'disabled' => ($selectedImageType !== null),
											]) !!}
										@else
											{!! Form::select('image_type', ["" => 'Select Image Type'] + $image_type,$selectedImageType, ['class' => 'form-control','id' => 'image_type']) !!}
										@endif
                                    </div>
                                    <!-- Error -->
                                    @if ($errors->has('image_type'))
                                    <div class="error">
                                        {{ $errors->first('image_type') }}
                                    </div>
                                    @endif
                                </div>
                            </div>
							<div class="col-12 col-sm-6 single">
                                <div class="form-group">
                                    <label for="audio_display_image">Audio Display Image<span class="error">*</span></label>
                                    <input type="file" name="audio_display_image" class="form-control" id="audio_display_image" accept="image/*"> 
                                    <!-- Error -->
                                    @if ($errors->first('audio_display_image'))
                                    <div class="error">
                                        {{ $errors->first('audio_display_image') }}
                                    </div>
                                    @endif
									@isset($formObj->toolMedia->audio_display_image)
                                        <img  src="{{$aws_path . $formObj->toolMedia->audio_display_image}}" width="150px" height="150px" class="img-thumbnail" alt="image">
                                    @endisset
                                </div>
                            </div>
							<div class="col-12 col-sm-6 multiple"></div>
						</div>
						@if(isset($selectedImageType) && $selectedImageType == "multiple")
							@foreach($slot_image as $key => $value)
								<div class="row slots timeline-border" name="slots" id="slots-{{$key}}">
									<div class="col-12 col-sm-3 multiple">
										<div class="form-group">
											<label for="start_time_{{$key}}">Start Time<span class="error">*</span></label>
											<input id="start_time_{{$key}}" type="text" name="start_time[]" class="timepicker form-control" data-name="start_time" data-sequence="{{$key}}" value="{{$value->start_time}}" required {{$key != 0 ? 'readonly' : ''}} />
											<!-- Error -->
											@if ($errors->first('start_time'))
												<div class="error">
													{{ $errors->first('start_time') }}
												</div>
											@endif
										</div>
									</div>
									<div class="col-12 col-sm-3 multiple">
										<div class="form-group">
											<label for="end_time_{{$key}}">End Time<span class="error">*</span></label>
											<input id="end_time_{{$key}}" type="text" name="end_time[]" class="end-time timepicker form-control" data-name="end_time" data-sequence = '{{$key}}' value="{{$value->end_time}}" required/>
											<!-- Error -->
											@if ($errors->first('end_time'))
												<div class="error">
													{{ $errors->first('end_time') }}
												</div>
											@endif
										</div>
									</div>
									<div class="col-12 col-sm-6 multiple">
										<div class="form-group">
											<label for="slot_image">Slot Image<span class="error">*</span></label>
											<input type="file" name="slot_image[]" class="form-control" id="slot_image" accept="image/*" data-name="slot_image" /> 
											<input type="hidden" name="slot_image[]" value="{{$value->image}}">
											<!-- Error -->
											@if ($errors->first('slot_image'))
											<div class="error">
												{{ $errors->first('slot_image') }}
											</div>
											@endif
										</div>
									</div>
									<div class="col-12 col-sm-3 multiple">
										<div class="form-group">
											<label for="ko_start_time_{{$key}}">시작 시간<span class="error">*</span></label>
											<input id="ko_start_time_{{$key}}" type="text" name="ko_start_time[]" class="timepicker form-control" data-name="ko_start_time" data-sequence="{{$key}}" value="{{$value->ko_start_time ?? ''}}" required {{$key != 0 ? 'readonly' : ''}} />
											<!-- Error -->
											@if ($errors->first('ko_start_time'))
												<div class="error">
													{{ $errors->first('ko_start_time') }}
												</div>
											@endif
										</div>
									</div>
									<div class="col-12 col-sm-3 multiple">
										<div class="form-group">
											<label for="ko_end_time_{{$key}}">종료 시간<span class="error">*</span></label>
											<input id="ko_end_time_{{$key}}" type="text" name="ko_end_time[]" class="end-time timepicker form-control" data-name="ko_end_time" data-sequence = '{{$key}}' value="{{$value->ko_end_time ?? ''}}" required/>
											<!-- Error -->
											@if ($errors->first('ko_end_time'))
												<div class="error">
													{{ $errors->first('ko_end_time') }}
												</div>
											@endif
										</div>
									</div>
									<div class="col-12 col-sm-3 multiple">
										<div class="form-group">
											@isset($value->image)
												<img src="{{$aws_path . $value->image}}" width="150px" height="150px" class="img-thumbnail" alt="image">
											@endisset
										</div>
									</div>
								</div>
							@endforeach
						@else
							<div class="row slots timeline-border multiple" name="slots" id="slots">
								<div class="col-12 col-sm-3 multiple">
									<div class="form-group">
										<label>Start Time<span class="error">*</span></label>
										<input id="start_time_0" type="text" name="start_time[]" class="timepicker form-control" data-name="start_time" data-sequence = '0'  required/>
										<!-- Error -->
										@if ($errors->first('start_time'))
											<div class="error">
												{{ $errors->first('start_time') }}
											</div>
										@endif
									</div>
								</div>
								<div class="col-12 col-sm-3 multiple">
									<div class="form-group">
										<label>End Time<span class="error">*</span></label>
										<input id="end_time_0" type="text" name="end_time[]" class="end-time timepicker form-control" data-name="end_time" data-sequence = '0' required/>
										<!-- Error -->
										@if ($errors->first('end_time'))
											<div class="error">
												{{ $errors->first('end_time') }}
											</div>
										@endif
									</div>
								</div>
								<div class="col-12 col-sm-6 multiple">
									<div class="form-group">
										<label for="slot_image">Slot Image<span class="error">*</span></label>
										<input type="file" name="slot_image[]" class="form-control" id="slot_image" accept="image/*" data-name="slot_image" required/> 
										<!-- Error -->
										@if ($errors->first('slot_image'))
										<div class="error">
											{{ $errors->first('slot_image') }}
										</div>
										@endif
									</div>
								</div>
								<div class="col-12 col-sm-3 multiple">
									<div class="form-group">
										<label>시작 시간<span class="error">*</span></label>
										<input id="ko_start_time_0" type="text" name="ko_start_time[]" class="timepicker form-control" data-name="ko_start_time" data-sequence = '0'  required/>
										<!-- Error -->
										@if ($errors->first('ko_start_time'))
											<div class="error">
												{{ $errors->first('ko_start_time') }}
											</div>
										@endif
									</div>
								</div>
								<div class="col-12 col-sm-3 multiple">
									<div class="form-group">
										<label>종료 시간<span class="error">*</span></label>
										<input id="ko_end_time_0" type="text" name="ko_end_time[]" class="end-time timepicker form-control" data-name="ko_end_time" data-sequence = '0' required/>
										<!-- Error -->
										@if ($errors->first('ko_end_time'))
											<div class="error">
												{{ $errors->first('ko_end_time') }}
											</div>
										@endif
									</div>
								</div>
							</div>
						@endif
						<div class="justify-content-end  multiple mt-3" style="">
							<span class="btn btn-success add-more"><i class="mr-2 fa fa-fw fa-lg fa-plus-circle"></i>Add Slot</span>
							<span class="btn btn-danger remove-slot d-none remove-btn"><i class="mr-2 fa fa-trash"></i>Remove Slot</span>
						</div>
					</div>
					<div class="card-footer">
						<button type="submit" class="mb-2 mr-2 btn btn-info">{{ __('messages.save_button') }}</button>
						<a href="{{ route('admin.tool.index') }}" class=" mb-2 mr-2 btn btn-danger icon-btn"><i class="fa fa-fw fa-lg fa-times-circle"></i>Cancel</a>
					</div>
					{{ Form::close() }}
				</div>
				<!-- /.card -->
			</div>
			<!--/.col (left) -->
		</div>
		<!-- /.row -->
	</div><!-- /.container-fluid -->
</section>
@stop
@push('js')
	@include('admin.partials.image_preview_script')

	<!-- jQuery CDN -->
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.js"></script>
	
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.2/dist/jquery.validate.min.js"></script>
	<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.js"></script>

	<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" />
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>

	<script>
		$( document ).ready(function ( ) {
			var id = "{{ $action_params }}";
			var oldData= "{{ $section }}";

			imageTypeChange();

			for (var i = 0; i <= oldData; i++) {
				/* setting time */
				$("#start_time_" + i).datetimepicker({
					icons: {
						up: 'fa fa-angle-up',
						down: 'fa fa-angle-down'
					},
					format: "HH:mm:ss"
				});

				$("#end_time_" + i).datetimepicker({
					icons: {
						up: 'fa fa-angle-up',
						down: 'fa fa-angle-down'
					},
					format: "HH:mm:ss"
				}).on("input dp.change", function (e) {
					const end_time_input = $(this); // Get the end_time input element
					const dataSequenceValue = end_time_input.data("sequence"); // Get the value of data-sequence attribute
					lang = 'en';
	
					// Call the function with both the value of the end_time and the data-sequence
					setNextFormDate(e.target.value, dataSequenceValue, lang);
				});

				$("#ko_start_time_" + i).datetimepicker({
					icons: {
						up: 'fa fa-angle-up',
						down: 'fa fa-angle-down'
					},
					format: "HH:mm:ss"
				});

				$("#ko_end_time_" + i).datetimepicker({
					icons: {
						up: 'fa fa-angle-up',
						down: 'fa fa-angle-down'
					},
					format: "HH:mm:ss"
				}).on("input dp.change", function (e) {
					const end_time_input = $(this); // Get the end_time input element
					const dataSequenceValue = end_time_input.data("sequence"); // Get the value of data-sequence attribute
					lang = 'ko';

					// Call the function with both the value of the end_time and the data-sequence
					setNextFormDate(e.target.value, dataSequenceValue, lang);
				});
			}

			$("form[name='tool_form']").validate({
				ignore: ":hidden",
				// Define validation rules
				rules: {
					title: {
						required: true
					},
					korean_title: {
						required: true
					},
					screen_image: {
						required: function(element) {
							return id === '';
						},
						accept: "image/*"
					},
					tool_audio: {
						required: {
							depends: function(element) {
								return $("#k_tool_audio").val().trim() !== ""; // Both fields are required if k_tool_audio is filled
							}
						},
						accept: "audio/*"
					},
					k_tool_audio: {
						required: {
							depends: function(element) {
								return $("#tool_audio").val().trim() !== ""; // Both fields are required if tool_audio is filled
							}
						},
						accept: "audio/*"
					},
					image_type: {
						required: {
							depends: function(element) {
								return $("#tool_audio").val().trim() !== "" && $("#k_tool_audio").val().trim() !== "";
							}
						}
					},
					audio_display_image: {
						required: function(element) {
							return id === '';
						},
						accept: "image/*"
					},
					'start_time[*]': {
						required: {
							depends: function(element) {
								return $("#image_type").val() === "multiple";
							}
						},
					},
					"end_time[]": {
						required: {
							depends: function(element) {
								return $("#image_type").val() === "multiple";
							}
						},
					},
					'ko_start_time[*]': {
						required: {
							depends: function(element) {
								return $("#image_type").val() === "multiple";
							}
						},
					},
					"ko_end_time[]": {
						required: {
							depends: function(element) {
								return $("#image_type").val() === "multiple";
							}
						},
					}
				},
				messages: {
					// Add custom error messages if needed
					title: {
						required: "The title field(in english) is required."
					},
					korean_title: {
						required: "The title field(in korean) is required."
					},
					screen_image: {
						required: "The screen image field is required."
					},
					tool_audio: {
						required: "Both audio fields (in English and Korean) are required or should be left empty."
					},
					k_tool_audio: {
						required: "Both audio fields (in English and Korean) are required or should be left empty."
					},
					image_type: {
						required: "Image Type is required if both audio fields are filled."
					},
					audio_display_image: {
						required: "The audio display image field is required when image type is single."
					}
				},
				submitHandler: function (form) {
					form.submit();
				}
			});

			
			var cloneCount = $('.slots').length;
			if(cloneCount > 1){
				$('.remove-slot').removeClass('d-none');
			}else{
				$('.remove-slot').addClass('d-none');
			}
		});

		$(document).on('change', '#image_type', function(e) {
			imageTypeChange();
		});

		function imageTypeChange(){
			var imageType = $('#image_type').val();
			if(imageType == 'single'){
				$('.single').show();
				$('.multiple').hide();
			}else if(imageType == 'multiple'){
				$('.single').hide();
				$('.multiple').show();
			}else{
				$('.single').hide();
				$('.multiple').hide();
			}
		}

		var count= "{{ $section }}";

		$(document).on('click', '.add-more', function(){
			count++;

			// Clone the tool-details section
			var clonedSection = $('.slots:last').clone();

			// Update the ID of the cloned section and its input fields
 			var newId = 'slots-' + count;
			clonedSection.attr('id', newId);
			clonedSection.find('input[type="text"]').each(function(index) {
				var fieldName = $(this).attr('data-name');
				var newFieldId = fieldName + '_' + count;
				$(this).attr('id', newFieldId);
				$(this).attr('name', fieldName+'['+count+']');
				$(this).attr('data-sequence', count);
				// Disable cloned inputs (except the start_time input)
				if (fieldName === 'start_time' || fieldName === 'ko_start_time') {
					$(this).prop('readonly', true);
				}
			});
			clonedSection.find('label.error').remove();
			clonedSection.find('img.img-thumbnail').remove();

			var fileInput = clonedSection.find('input[type="file"]');
			var fieldName = fileInput.attr('data-name');
			var newFieldId = fieldName + '_' + count;
			fileInput.attr('id', newFieldId);
			fileInput.attr('name', fieldName+'['+count+']');

			// Reset input values in the cloned section
			clonedSection.find('input').val('');

			// Append the cloned section after the last slots div
			clonedSection.insertAfter('.slots:last');

			// Reinitialize datetimepicker for the cloned input element
			$("#" + newId + " input[type='text']").datetimepicker({
				icons: {
					up: 'fa fa-angle-up',
					down: 'fa fa-angle-down'
				},
				format: "HH:mm:ss"
			}).on("input dp.change", function (e) {
				const end_time_input = $(this); // Get the end_time input element
				const langField = end_time_input.data("name");
 		 		const dataSequenceValue = end_time_input.data("sequence"); // Get the value of data-sequence attribute

				const lang = (langField == 'end_time') ? 'en' : 'ko';
				// Call the function with both the value of the end_time and the data-sequence
				setNextFormDate(e.target.value, dataSequenceValue, lang);
			});

			clonedSection.find('input[type="text"]').each(function(index) {
				var fieldName = $(this).attr('data-name');
				// Disable cloned inputs (except the start_time input)
				if (fieldName === 'start_time' || fieldName === 'ko_start_time') {
					var newFieldId = fieldName + '_' + count;
					const end_time_input = $(this); // Get the end_time input element
					const dataSequenceValue = end_time_input.data("sequence"); // Get the value of data-sequence attribute
					const previousInput = dataSequenceValue - 1;
					const finalVal = (fieldName === 'start_time') ? ($("#end_time_"+(previousInput)).val()) : $("#ko_end_time_"+(previousInput)).val();
					const lang = (fieldName === 'start_time') ? 'en' : 'ko';
					setNextFormDate(finalVal, previousInput, lang);
				}
			});
			var cloneCount = $('.slots').length;
			if(cloneCount > 1){
				$('.remove-slot').removeClass('d-none');
			}else{
				$('.remove-slot').addClass('d-none');
			}
		});

		$(document).on('click', '.remove-btn', function() {
			// Get the parent tool-details div
			$('.slots:last').remove();

			var cloneCount = $('.slots').length;
			count = (cloneCount - 1);
			if(cloneCount > 1){
				$('.remove-slot').removeClass('d-none');
			}else{
				$('.remove-slot').addClass('d-none');
			}
        });

		function setNextFormDate(time, sequence, lang){
			const timeObj = new Date(`2000-01-01T${time}`);

			// Add 1 second to the time
			timeObj.setSeconds(timeObj.getSeconds() + 1);

			// Format the time back to "HH:mm:ss" format
			const nextTime = timeObj.toTimeString().slice(0, 8);
			const nextSequence = sequence + 1;

			// Set the next time
			if(lang == 'ko'){
				$("#ko_start_time_"+nextSequence).val(nextTime);
			}else{
				$("#start_time_"+nextSequence).val(nextTime);
			}
		}
	</script>
@endpush
