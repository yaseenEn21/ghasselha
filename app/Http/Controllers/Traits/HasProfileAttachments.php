<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use RuntimeException;

trait HasProfileAttachments
{
    /**
     * اسم البارامتر في الراوت
     * مثال: insurance / accident / customer
     */
    protected string $profileAttachmentRouteKey = 'model';

    /**
     * كلاس الموديل اللي عليه المرفقات
     * لازم يتضبط في الكنترولر اللي بيستخدم التريت
     * مثال: \App\Models\Insurance::class
     */
    protected ?string $profileAttachmentModelClass = null;

    /**
     * اسم كولكشن المرفقات في Media Library
     */
    protected string $profileAttachmentCollection = 'attachments';

    /**
     * جلب الموديل من الـ Route (ID أو Model)
     */
    protected function getProfileAttachmentOwner(Request $request): Model
    {
        $param = $this->profileAttachmentRouteKey;          // مثال: 'insurance'
        $value = $request->route($param);                   // ممكن يكون ID أو Model
        $modelClass = $this->profileAttachmentModelClass;   // مثال: Insurance::class

        if ($value instanceof Model) {
            return $value;
        }

        if (!$modelClass || !is_subclass_of($modelClass, Model::class)) {
            throw new RuntimeException(
                "Property [profileAttachmentModelClass] is not set correctly on [" . static::class . "]."
            );
        }

        if (is_scalar($value) || is_string($value)) {
            /** @var \Illuminate\Database\Eloquent\Model $model */
            $model = $modelClass::query()->findOrFail($value);
            return $model;
        }

        throw new RuntimeException(
            "Route param [{$param}] could not be resolved to model [{$modelClass}]."
        );
    }

    public function profileAttachments(Request $request)
    {
        $owner = $this->getProfileAttachmentOwner($request);

        $mediaItems = $owner
            ->getMedia($this->profileAttachmentCollection)
            ->map(function (Media $m) {
                return [
                    'id' => $m->id,
                    'name' => $m->file_name,
                    'size_human' => $m->human_readable_size,
                    'mime' => $m->mime_type,
                    'url' => $m->getFullUrl(),
                    'is_image' => str_starts_with($m->mime_type, 'image/'),
                    'created_at' => $m->created_at->toDateTimeString(),
                ];
            })
            ->values();

        return response()->json(['data' => $mediaItems]);
    }

    public function profileAttachmentsStore(Request $request)
    {
        $owner = $this->getProfileAttachmentOwner($request);

        $request->validate([
            'attachments.*' => 'file|mimetypes:image/*,video/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document|max:512000'
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $owner
                    ->addMedia($file)
                    ->toMediaCollection($this->profileAttachmentCollection);
            }
        }

        return response()->json(['message' => 'تم إضافة المرفقات بنجاح']);
    }

    public function profileAttachmentDestroy(Request $request)
    {
        $owner = $this->getProfileAttachmentOwner($request);

        $mediaId = $request->route('media');

        \Log::info('profileAttachmentDestroy check', [
            'route_name' => $request->route()->getName(),
            'route_param_key' => $this->profileAttachmentRouteKey,
            'route_param_value' => $request->route($this->profileAttachmentRouteKey),

            'owner_class' => get_class($owner),
            'owner_id' => $owner->getKey(),

            'requested_media_id' => $mediaId,
            'owner_media_ids' => $owner->media->pluck('id')->all(),
            'exists_on_owner' => $owner->media()->whereKey($mediaId)->exists(),
        ]);

        $media = $owner->media()->whereKey($mediaId)->firstOrFail();

        $media->delete();

        \Log::info('profileAttachmentDestroy success', [
            'owner_class' => get_class($owner),
            'owner_id' => $owner->getKey(),
            'media_id' => $media->id,
        ]);

        return response()->json(['message' => 'تم حذف المرفق']);
    }



}
