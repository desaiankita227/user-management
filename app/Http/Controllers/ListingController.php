<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use DataTables;

class ListingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $model = Listing::query();

            return DataTables::eloquent($model)
                ->addColumn('action', function (Listing $row) {
                    return view(
                        "partials.action",
                        [
                            'currentRoute'  => "listings",
                            'row'           => $row,
                            'isDelete'      => 1,
                            'isEdit'        => 1,
                        ]
                    )->render();
                })
                // ->editColumn('screen_image', function ($row) {
                //     if($row->screen_image){
                //         $file_path  =   env('CLOUD_FRONT_URL').'/'.$this->path.$row->id.'/'.$row->screen_image;
                //         return '<img src=" ' . $file_path . ' " width="100px" height="100px" class="img-thumbnail" alt="image">';
                //     }
                // })
                // ->rawColumns(['action', 'screen_image'])
                ->rawColumns(['action'])
                ->make(true);
        } else {
            $data = array();
            return view('listing.index', $data);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = array(
            "formObj"            => $this->modelObj,
            "module"             => $this->module,
            "page_title"         => "Create",
            "action_url"         => $this->moduleRouteText . ".store",
            "action_params"      => $this->modelObj->id,
            "method"             => "POST",
            "image_type"         => ["single" => "Single Image", "multiple" => "Image with Time Slot"],
            "selectedImageType"  => null,
            "section"            => 0
        );

        return view($this->moduleViewName . '.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ToolRequest $request)
    {
        // return $request;
        $tool = new Tool();
        $tool->slug           = Str::slug($request->title);
        $tool->title          = $request->title;
        $tool->k_title        = $request->korean_title;
        $tool->sequence       = Commonhelper::maxToolSequence();
        $tool->information    = $request->information;
        $tool->k_information  = $request->k_information;
        $tool->save();

        $tool_id = $tool->id;
        if( $request->has('screen_image') && $request->file('screen_image') )
        {
            $imageFile = $request->file('screen_image');
            $image = AwsHelper::UploadFileS3('image',$imageFile,$this->path.$tool_id.'/',null,$tool_id);

            $fileName = $image['fileName'];
           
            if ($image['code'] == 200) {
                $tool->screen_image = $fileName;
                $tool->save();
            }
        }

        if($request->has('tool_audio') && $request->tool_audio != null && $request->has('k_tool_audio') && $request->k_tool_audio != null)
        {
            $toolMedia = new ToolMedia();
            $toolMedia->tool_id = $tool_id;
            $toolMedia->image_type = $request->image_type;
            if($request->has('tool_audio'))
            {
                $file = $request->file('tool_audio');
                $audio = AwsHelper::UploadFileS3('audio',$file,$this->path.$tool_id.'/',null,$tool_id);
             
                $fileName = $audio['fileName'];
                
                if ($audio['code'] == 200) {
                    $toolMedia->tool_audio = $fileName;
                }
            }   

            if($request->has('k_tool_audio'))
            {
                $file = $request->file('k_tool_audio');
                $audio = AwsHelper::UploadFileS3('audio',$file,$this->path.$tool_id.'/',null,$tool_id);
             
                $fileName = $audio['fileName'];
                
                if ($audio['code'] == 200) {
                    $toolMedia->k_tool_audio = $fileName;
                }
            }
            if($request->has('image_type') && $request->image_type == "single" &&  $request->has('audio_display_image') && $request->file('audio_display_image') ) {
                $imageFile = $request->file('audio_display_image');
                $image = AwsHelper::UploadFileS3('image',$imageFile,$this->path.$tool_id.'/',null,$tool_id);
    
                $fileName = $image['fileName'];
                
                if ($image['code'] == 200) {
                    $toolMedia->audio_display_image = $fileName;
                }
            } else if($request->has('image_type') && $request->image_type == "multiple"){
                $timeline = [];
                foreach ($request->file('slot_image') as $key => $imageFile) {
                    $image = AwsHelper::UploadFileS3('image',$imageFile,$this->path.$tool_id.'/',null,$tool_id);
                    $timeline[$key]['start_time'] = $request->start_time[$key];
                    $timeline[$key]['end_time']   = $request->end_time[$key];
                    $timeline[$key]['ko_start_time'] = $request->ko_start_time[$key];
                    $timeline[$key]['ko_end_time']   = $request->ko_end_time[$key];
                    $timeline[$key]['image'] = $image['fileName'];
                }
                $timelineJson = json_encode($timeline);
                $toolMedia->timeline = $timelineJson;
            }
            $toolMedia->save();
        }
        return redirect()->route($this->moduleViewName . '.index')->with('success', __('messages.create_message', ['title' => 'tool']));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $tool = Tool::with('toolMedia')->find($id);

        if(!$tool){
            abort(404);
        }

        $data = array(
            "formObj"            => $tool,
            "module"             => $this->module,
            "page_title"         => "Update",
            "action_url"         => $this->moduleRouteText . ".update",
            "action_params"      => $tool->id,
            "method"            => "PUT",
            "image_type"         => ["single" => "Single Image", "multiple" => "Image with Time Slot"],
            "selectedImageType"  => isset($tool->toolMedia) ? $tool->toolMedia->image_type : null,
            "screen_image"       => env('CLOUD_FRONT_URL').'/'.$this->path.$tool->id.'/'.$tool->screen_image,
            "tool_audio"         => isset($tool->toolMedia) ? (env('CLOUD_FRONT_URL').'/'.$this->path.$tool->id.'/'.$tool->toolMedia['tool_audio']) : null,
            "k_tool_audio"       => isset($tool->toolMedia) ? (env('CLOUD_FRONT_URL').'/'.$this->path.$tool->id.'/'.$tool->toolMedia['k_tool_audio']) : null,
            "section"            => (isset($tool->toolMedia) && isset($tool->toolMedia->timeline)) ? (count($tool->toolMedia->timeline) > 1 ? (count($tool->toolMedia->timeline) - 1) : 0): 0,
            "slot_image"         => isset($tool->toolMedia) ? $tool->toolMedia->timeline : null,
            "aws_path"           => env('CLOUD_FRONT_URL').'/'.$this->path.$tool->id.'/'
        );

        return view($this->moduleViewName . '.create', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ToolRequest $request, $id)
    {
        try{
            $tool = Tool::find($id);
            if(!$tool){
                abort(404);
            }

            $oldFileName = $tool->screen_image;

            $tool->title = $request->title;
            $tool->k_title = $request->korean_title;
            $tool->information = $request->information;
            $tool->k_information = $request->k_information;
            $tool->save();

            if( $request->has('screen_image') && $request->file('screen_image') )
            {
                if($tool->screen_image) {
                    $file = AwsHelper::deleteFileS3($this->path.$id.'/',null,$tool->screen_image);
                }
                $imageFile = $request->file('screen_image');
                $image = AwsHelper::UploadFileS3('image',$imageFile,$this->path.$id.'/',null,$id);
    
                $fileName = $image['fileName'];
               
                if ($image['code'] == 200) {
                    $tool->screen_image = $fileName;
                    $tool->save();
                }
            }
            $toolMedia = ToolMedia::where('tool_id', $id)->first();

            if($toolMedia && $toolMedia->image_type == "single" &&  $request->has('image_type') && $request->image_type == "single" &&  $request->has('audio_display_image') ){
                $file = AwsHelper::deleteFileS3($this->path.$id.'/',null,$toolMedia->audio_display_image);
            }

            if($toolMedia && $toolMedia->image_type == "multiple" &&  $request->has('image_type') && $request->image_type == "multiple"){
                // Extract images from the arra
                $dataArray = $toolMedia->timeline;

                if($dataArray){
                    $images = [];
                    foreach ($dataArray as $item) {
                        $images[] = $item->image;
                    }
                        
                    $nonExistingValues = array_diff($images, $request->slot_image);
    
                    // If you want the result as an indexed array (with numeric keys), you can reindex it.
                    $result = array_values($nonExistingValues);
                    if(count($result) > 0) {
                        foreach ($result as $key => $value) {
                            $file = AwsHelper::deleteFileS3($this->path.$id.'/',null,$value);
                        }
                    }
                }
            }

            if(($request->has('tool_audio') && $request->tool_audio != null) || ($request->has('k_tool_audio') && $request->k_tool_audio != null) || ($request->has('image_type') && $request->image_type != "") )  {
                $toolMedia = ($toolMedia) ? $toolMedia : new ToolMedia();
                $toolMedia->tool_id = $id;
                $toolMedia->image_type = $request->image_type;
                if($request->has('tool_audio'))
                {
                    if($toolMedia->tool_audio && $toolMedia->tool_audio != null && $request->tool_audio != null) {
                        $file = AwsHelper::deleteFileS3($this->path.$id.'/',null,$toolMedia->tool_audio);
                    }

                    $file = $request->file('tool_audio');
                    $audio = AwsHelper::UploadFileS3('audio',$file,$this->path.$id.'/',null,$id);
                 
                    $fileName = $audio['fileName'];
                    
                    if ($audio['code'] == 200) {
                        $toolMedia->tool_audio = $fileName;
                    }
                }   

                if($request->has('k_tool_audio'))
                {
                    if($toolMedia->k_tool_audio && $toolMedia->k_tool_audio != null && $request->k_tool_audio != null) {
                        $file = AwsHelper::deleteFileS3($this->path.$id.'/',null,$toolMedia->k_tool_audio);
                    }
                    $file = $request->file('k_tool_audio');
                    $audio = AwsHelper::UploadFileS3('audio',$file,$this->path.$id.'/',null,$id);
                 
                    $fileName = $audio['fileName'];
                    
                    if ($audio['code'] == 200) {
                        $toolMedia->k_tool_audio = $fileName;
                    }
                }

                if($request->has('image_type') && $request->image_type == "single" &&  $request->has('audio_display_image') && $request->file('audio_display_image') ) {
                    $imageFile = $request->file('audio_display_image');
                    $image = AwsHelper::UploadFileS3('image',$imageFile,$this->path.$id.'/',null,$id);

                    $fileName = $image['fileName'];

                    if ($image['code'] == 200) {
                        $toolMedia->audio_display_image = $fileName;
                    }
                } else if($request->has('image_type') && $request->image_type == "multiple"){
                    $timeline = [];
                    foreach ($request->slot_image as $key => $imageFile) {
                        if(is_string($imageFile)){
                            $timeline[$key]['start_time'] = $request->start_time[$key];
                            $timeline[$key]['end_time']   = $request->end_time[$key];
                            $timeline[$key]['ko_start_time'] = $request->ko_start_time[$key];
                            $timeline[$key]['ko_end_time']   = $request->ko_end_time[$key];
                            $timeline[$key]['image'] = $imageFile;
                        }
                    }
                    if($request->hasFile('slot_image')){
                        foreach ($request->file('slot_image') as $key => $imageFile) {
                            $image = AwsHelper::UploadFileS3('image',$imageFile,$this->path.$id.'/',null,$id);
                            $timeline[$key]['start_time'] = $request->start_time[$key];
                            $timeline[$key]['end_time']   = $request->end_time[$key];
                            $timeline[$key]['ko_start_time'] = $request->ko_start_time[$key];
                            $timeline[$key]['ko_end_time']   = $request->ko_end_time[$key];
                            $timeline[$key]['image'] = $image['fileName'];
                        }
                    }
                    $timeline = array_values($timeline); // Re-index the array to start from 0
                    $timelineJson = json_encode($timeline, JSON_PRETTY_PRINT);
                    $toolMedia->timeline = $timelineJson;
                }
                $toolMedia->save();
            }
        }catch (\Exception $e) {
            return redirect()->with('error', $e->getMessage());
        }
        return redirect()->route($this->moduleViewName . '.index')->with('success', __('messages.update_message', ['title' => 'episode']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $tool = Tool::find($id);

        $path = $this->path.$id.'/';
        if(!empty($tool->screen_image)) {
            $file = AwsHelper::deleteDirectoryS3($path);
        }
        $tool->delete();
        $toolMedia = ToolMedia::where('tool_id',$id)->delete();
        return response()->json(['code' => 200, 'message' => __('messages.delete_message', ['title' => 'Tool']), 'data' => array()]);
    }
    public function updateSequence(Request $request)
    {
        $order = $request->input('order');

        foreach ($order as $index => $id) {
            $sequence = $index + 1;
            Tool::where('id', $id)->update(['sequence' => $sequence]);
        }
    
        return response()->json(['success' => true]);
    }
}