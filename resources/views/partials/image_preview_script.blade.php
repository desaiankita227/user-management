<script>
    $(function() {
        // Multiple images preview with JavaScript
        var multiImgPreview = function(input, imgPreviewPlaceholder) {

            if (input.files) {
                var filesAmount = input.files.length;
                $('.imgPreview').html('');
                for (i = 0; i < filesAmount; i++) {
                    var reader = new FileReader();
                    reader.onload = function(event) {
                        var image = reader.result;
                        var dataSplit = image.split("/", 1)
                        if(dataSplit[0] == 'data:image'){
                            var image = '<img width="150px" height="150px" class="img-thumbnail" alt="image">'
                            $($.parseHTML(image)).attr('src', event.target.result).appendTo(imgPreviewPlaceholder);
                        }
                        if(dataSplit[0] == 'data:video'){
                            var video = '<video width="150px" height="150px" controls><source type="video/mp4"></video>';
                            $($.parseHTML(video)).attr('src', event.target.result).appendTo(imgPreviewPlaceholder);
                        }
                        if(dataSplit[0] == 'data:audio'){
                            var audio = '<audio controls><source type="audio/mp3"></audio>';
                            $($.parseHTML(audio)).attr('src', event.target.result).appendTo(imgPreviewPlaceholder);
                        }
                    }

                    reader.readAsDataURL(input.files[i]);
                }
            }

        };

        $('#images').on('change', function() {
            multiImgPreview(this, 'div.imgPreview');
        });

    });
    {{--  function deleteImage(id){
        $.ajax({
            type: "GET",
            url: "{{ route('admin.admin.removeimage') }}/"+id,
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            dataType: 'JSON',
            success: function (response) {
                if (response.code == 200) {
                    $("#removecol_"+id).remove();
                }
            }
        });
    }  --}}
</script>