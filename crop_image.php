 <?php
//  =========  Crop Image with Laravel project  =============//
#===============================================================================
# call "imgareaselect.css" file inside of the html head
# call jQuery Library & included "jquery.imgareaselect.min.js" file last of the body
# Example...
<link href="{{ asset('backend') }}/css/imageselect_crop/imgareaselect.css" rel="stylesheet">
<script src="{{ asset('backend') }}/js/jQuery-3.3.1.min.js"></script>
<script src="{{ asset('backend') }}/js/jquery.imgareaselect.min.js"></script>

#===============================================================================
// in HTML or "file_name.blade.php" include input file & other input options
// also need to include some JavaScript & jQuery code

# Example...
# HTML
<div class="row mb-5">
    <div class="col-12">
        <div class="form-group mt-4">
            <label>Photo</label>
            <input type="file" id="inputImage" class="form-control image" name="photo">
            <input type="hidden" name="x1" value="" />
            <input type="hidden" name="y1" value="" />
            <input type="hidden" name="w" value="" />
            <input type="hidden" name="h" value="" />
            @if($errors->has("photo"))
                <small class="text-danger form-text error_text">{{ $errors->first("photo") }}</small>
            @endif
        </div>
    </div>
    <div class="col-12 text-center">
        <img id="previewimage" src="{{ asset('uploads') }}/user/avatar.jpg" style="width:400px; border:1px solid #ddd" />
    </div>
</div>

# Example...
# JS
@section("footer_script")
<script src="{{ asset('js/jquery.imgareaselect.min.js') }}"></script>
<script>
    jQuery(function($) {

        var p = $("#previewimage");
        $("body").on("change", ".image", function() {
            var imageReader = new FileReader();
            imageReader.readAsDataURL(document.querySelector(".image").files[0]);
            imageReader.onload = function(oFREvent) {
                p.attr('src', oFREvent.target.result).fadeIn();
            };
        });

        $('#previewimage').imgAreaSelect({
            // maxWidth: '250',
            // maxHeight: '250',
            aspectRatio: "1:1",
            onSelectEnd: function(img, selection) {
                $('input[name="x1"]').val(selection.x1);
                $('input[name="y1"]').val(selection.y1);
                $('input[name="w"]').val(selection.width);
                $('input[name="h"]').val(selection.height);
            }
        });

    });
</script>
@endsection

#===============================================================================
// Controller -- data post from controller
# Example...
public function add_user_post(Request $request)
{
    if ($request->hasFile("photo")) {
        $extension = $request->file('photo')->getClientOriginalExtension();
        $photo_new_name = "user_".$insert_id.".".$extension;

        //Upload Image file for crop from here (after crop & save it will delete)
        // here use "Intervention" Image upload package
        Image::make($request->file('photo'))->widen(400)->save(public_path("uploads/".$photo_new_name));

        $photo_save_path = public_path("uploads/user/".$photo_new_name);

        $width = $request->input('w');
        $height = $request->input('h');
        if ($width == "" && $height == "") {
            Image::make($request->file('photo'))->heighten(200)->save($photo_save_path);
            User::findOrFail($insert_id)->update([
                "photo"=>$photo_new_name,
                "updated_at"=>Carbon::now(),
            ]);
            unlink(public_path("uploads/".$photo_new_name));
        } else {
            $for_crop_path = Image::make(public_path("uploads/".$photo_new_name));
            $for_crop_path->crop($request->input('w'), $request->input('h'), $request->input('x1'), $request->input('y1'))->heighten(200)->save($photo_save_path);
            User::findOrFail($insert_id)->update([
                "photo"=>$photo_new_name,
                "updated_at"=>Carbon::now(),
            ]);
            unlink(public_path("uploads/".$photo_new_name));
        };
    };

    if ($insert_id) {
        Session::flash("success", "Successfully Register User information !");
        return back();
    } else {
        Session::flash("unsuccess", "User Registration Failed !");
        return back();
    };
}

#===============================================================================
#=== END ====
