<x-app-layout>
	{{ Html::style('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css') }}
    {{-- Styles --}}
    {{ Html::style('https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css') }}

	<x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Add Listing') }}
            </h2>
            <a class="btn btn-info" href="{{ route('listings.index') }}">
                Go To Listing
            </a>
        </div>
    </x-slot>

	<div class="py-12">
		{{ Form::open(array('url' => route($action_url, $action_params), 'method'=> $method, 'enctype' => 'multipart/form-data', 'class' => 'form-vertical',
						'name' => 'tool_form')) }}
			@if ($method === "PUT")
			<input type="hidden" id="id" name="id" value="{{ $action_params }}">
			@endif
			<!-- form start -->
			<div class="card-body">
				<div class="row">
					<div class="col-12 col-sm-6">
						<div class="form-group">
							<label for="title">Title<span class="error">*</span></label>
							{{ Form::text('title',(old('title'))?old('title'):$formObj->title, ['class' => 'form-control', 'placeholder' => 'Title', 'id' => 'title']) }}
							<!-- Error -->
							@if ($errors->has('title'))
							<div class="error">
								{{ $errors->first('title') }}
							</div>
							@endif
						</div>
					</div>
					<!-- Select Agent -->
					<div class="col-12 col-sm-6">
						<div class="form-group">
							<label for="user_id">Select User<span class="error">*</span></label>
							{{ Form::select('user_id', $users, (old('user_id')) ? old('user_id') : $formObj->user_id, ['class' => 'form-control', 'id' => 'user_id']) }}
							<!-- Error -->
							@if ($errors->has('user_id'))
							<div class="error">
								{{ $errors->first('user_id') }}
							</div>
							@endif
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-12 col-sm-6">
						<div class="form-group">
							<label for="description">Description</label>
							{{ Form::textarea('description', (old('description')) ? old('description') : $formObj->description, ['class' => 'form-control', 'placeholder' => 'Description', 'id' => 'description']) }}
							<!-- Error -->
							@if ($errors->has('description'))
							<div class="error">
								{{ $errors->first('description') }}
							</div>
							@endif
						</div>
					</div>
					<!-- Price -->
					<div class="col-12 col-sm-6">
						<div class="form-group">
							<label for="price">Price<span class="error">*</span></label>
							{{ Form::text('price', (old('price')) ? old('price') : $formObj->price, ['class' => 'form-control', 'placeholder' => 'Price', 'id' => 'price']) }}
							<!-- Error -->
							@if ($errors->has('price'))
							<div class="error">
								{{ $errors->first('price') }}
							</div>
							@endif
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-12 col-sm-6">
						<div class="form-group">
							<label for="file">Image<span class="error">*</span></label>
							<input type="file" name="image" class="form-control" id="image" accept="image/*" />
							<!-- Error -->
							@if ($errors->first('image'))
							<div class="error">
								{{ $errors->first('image') }}
							</div>
							@endif
							<div class="imgPreview">
								@isset($formObj->image)
									<img src="{{$image}}" width="150px" height="150px" class="img-thumbnail" alt="image">
								@endisset
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="card-footer">
				<button type="submit" class="mb-2 mr-2 btn btn-info">Save</button>
				<a href="{{ route('listings.index') }}" class=" mb-2 mr-2 btn btn-danger icon-btn"><i class="fa fa-fw fa-lg fa-times-circle"></i>Cancel</a>
			</div>
		{{ Form::close() }}
	</div>
    @push('js')
        {{-- ✅ Load jQuery first, without integrity attribute --}}
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

</x-app-layout>
