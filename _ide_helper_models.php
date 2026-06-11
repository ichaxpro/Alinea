<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $key
 * @property string $title
 * @property string $description
 * @property string|null $icon
 * @property string $criteria_type
 * @property int $criteria_value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereCriteriaType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereCriteriaValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereUpdatedAt($value)
 */
	class Achievement extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $blocked_user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $blockedUser
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Block newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Block newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Block query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Block whereBlockedUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Block whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Block whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Block whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Block whereUserId($value)
 */
	class Block extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nama_klub
 * @property string $kategori
 * @property string $deskripsi
 * @property string|null $foto_klub
 * @property string $gradient_from
 * @property string $gradient_to
 * @property int $id_owner
 * @property int|null $admin_id
 * @property int $member_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $admin
 * @property-read \App\Models\User $owner
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookClub newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookClub newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookClub onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookClub query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookClub whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookClub whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookClub whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookClub whereDeskripsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookClub whereFotoKlub($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookClub whereGradientFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookClub whereGradientTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookClub whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookClub whereIdOwner($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookClub whereKategori($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookClub whereMemberCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookClub whereNamaKlub($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookClub whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookClub withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookClub withoutTrashed()
 */
	class BookClub extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $book_identifier
 * @property string $book_identifier_type
 * @property int $user_id
 * @property int $rating
 * @property string $ulasan
 * @property int $helpful
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read string $book_title
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookReview newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookReview newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookReview onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookReview query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookReview whereBookIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookReview whereBookIdentifierType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookReview whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookReview whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookReview whereHelpful($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookReview whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookReview whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookReview whereUlasan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookReview whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookReview whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookReview withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookReview withoutTrashed()
 */
	class BookReview extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $book_identifier
 * @property string $identifier_type
 * @property string $judul
 * @property string|null $penulis
 * @property string|null $foto_sampul
 * @property string|null $kategori
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bookmark newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bookmark newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bookmark query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bookmark whereBookIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bookmark whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bookmark whereFotoSampul($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bookmark whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bookmark whereIdentifierType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bookmark whereJudul($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bookmark whereKategori($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bookmark wherePenulis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bookmark whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bookmark whereUserId($value)
 */
	class Bookmark extends \Eloquent {}
}

namespace App\Models{
/**
 * @deprecated Use Message instead. This file is kept only for reference.
 * TODO: delete this file.
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage query()
 */
	class ChatMessage extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $judul
 * @property string $penulis
 * @property string|null $penerbit
 * @property int|null $tahun
 * @property string|null $isbn
 * @property int|null $jumlah_halaman
 * @property string|null $bahasa
 * @property string|null $kategori
 * @property string $status
 * @property numeric $rating_avg
 * @property int $rating_count
 * @property string $sinopsis
 * @property array<array-key, mixed>|null $genres
 * @property string|null $cover_url
 * @property string $gradient_from
 * @property string $gradient_to
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeaturedBook newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeaturedBook newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeaturedBook query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeaturedBook whereBahasa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeaturedBook whereCoverUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeaturedBook whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeaturedBook whereGenres($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeaturedBook whereGradientFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeaturedBook whereGradientTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeaturedBook whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeaturedBook whereIsbn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeaturedBook whereJudul($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeaturedBook whereJumlahHalaman($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeaturedBook whereKategori($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeaturedBook wherePenerbit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeaturedBook wherePenulis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeaturedBook whereRatingAvg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeaturedBook whereRatingCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeaturedBook whereSinopsis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeaturedBook whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeaturedBook whereTahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeaturedBook whereUpdatedAt($value)
 */
	class FeaturedBook extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $follower_id
 * @property int $following_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $follower
 * @property-read \App\Models\User $following
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follow newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follow newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follow query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follow whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follow whereFollowerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follow whereFollowingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follow whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follow whereUpdatedAt($value)
 */
	class Follow extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $sender_id
 * @property int $receiver_id
 * @property string $message
 * @property string|null $media_url
 * @property string|null $media_type
 * @property string|null $media_original_name
 * @property \Illuminate\Support\Carbon|null $read_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $receiver
 * @property-read \App\Models\User $sender
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereMediaOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereMediaType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereMediaUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereReceiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereSenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message withoutTrashed()
 */
	class Message extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $judul
 * @property string $penulis
 * @property string|null $isbn
 * @property int|null $tahun_terbit
 * @property string $kategori
 * @property string|null $cover_url
 * @property int|null $jumlah_halaman
 * @property bool $is_available
 * @property string $status
 * @property string|null $reading_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalBook newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalBook newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalBook query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalBook whereCoverUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalBook whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalBook whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalBook whereIsAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalBook whereIsbn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalBook whereJudul($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalBook whereJumlahHalaman($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalBook whereKategori($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalBook wherePenulis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalBook whereReadingStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalBook whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalBook whereTahunTerbit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalBook whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalBook whereUserId($value)
 */
	class PersonalBook extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $id_user
 * @property int $id_post
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TimelinePost|null $post
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostBookmark newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostBookmark newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostBookmark query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostBookmark whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostBookmark whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostBookmark whereIdPost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostBookmark whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostBookmark whereUpdatedAt($value)
 */
	class PostBookmark extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $reporter_id
 * @property int $reported_user_id
 * @property string|null $reason
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $reportedUser
 * @property-read \App\Models\User $reporter
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereReportedUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereReporterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereUpdatedAt($value)
 */
	class Report extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $reporter_id
 * @property int $post_id
 * @property string $reason
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TimelinePost|null $post
 * @property-read \App\Models\User $reporter
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportPost newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportPost newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportPost query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportPost whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportPost whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportPost wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportPost whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportPost whereReporterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportPost whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportPost whereUpdatedAt($value)
 */
	class ReportPost extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $attachable_type
 * @property int $attachable_id
 * @property string $path
 * @property string|null $type
 * @property string|null $original_name
 * @property int|null $size
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $attachable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineAttachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineAttachment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineAttachment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineAttachment whereAttachableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineAttachment whereAttachableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineAttachment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineAttachment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineAttachment whereOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineAttachment wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineAttachment whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineAttachment whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineAttachment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineAttachment whereUpdatedAt($value)
 */
	class TimelineAttachment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $id_post
 * @property int $id_user
 * @property string $isi_komentar
 * @property string|null $media
 * @property string|null $media_type
 * @property string|null $media_original_name
 * @property int|null $media_size
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TimelineAttachment> $attachments
 * @property-read int|null $attachments_count
 * @property-read \App\Models\User $author
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TimelineCommentLike> $likes
 * @property-read int|null $likes_count
 * @property-read \App\Models\TimelinePost|null $post
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineComment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineComment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineComment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineComment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineComment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineComment whereIdPost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineComment whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineComment whereIsiKomentar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineComment whereMedia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineComment whereMediaOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineComment whereMediaSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineComment whereMediaType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineComment whereUpdatedAt($value)
 */
	class TimelineComment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $id_comment
 * @property int $id_user
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TimelineComment $comment
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineCommentLike newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineCommentLike newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineCommentLike query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineCommentLike whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineCommentLike whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineCommentLike whereIdComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineCommentLike whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineCommentLike whereUpdatedAt($value)
 */
	class TimelineCommentLike extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $id_post
 * @property int $id_user
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TimelinePost|null $post
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineLike newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineLike newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineLike query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineLike whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineLike whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineLike whereIdPost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineLike whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineLike whereUpdatedAt($value)
 */
	class TimelineLike extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $id_user
 * @property int|null $id_klub
 * @property string|null $judul_buku_dibahas
 * @property string $pesan
 * @property string|null $tag
 * @property string|null $media
 * @property string|null $media_type
 * @property string|null $media_original_name
 * @property int|null $media_size
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TimelineAttachment> $attachments
 * @property-read int|null $attachments_count
 * @property-read \App\Models\User $author
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $bookmarkedBy
 * @property-read int|null $bookmarked_by_count
 * @property-read \App\Models\BookClub|null $club
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TimelineComment> $comments
 * @property-read int|null $comments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TimelineLike> $likes
 * @property-read int|null $likes_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelinePost newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelinePost newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelinePost onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelinePost query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelinePost whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelinePost whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelinePost whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelinePost whereIdKlub($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelinePost whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelinePost whereJudulBukuDibahas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelinePost whereMedia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelinePost whereMediaOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelinePost whereMediaSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelinePost whereMediaType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelinePost wherePesan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelinePost whereTag($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelinePost whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelinePost withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelinePost withoutTrashed()
 */
	class TimelinePost extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $book_id
 * @property int $borrower_id
 * @property int $owner_id
 * @property string|null $status
 * @property string|null $tanggal_pinjam_rencana
 * @property string|null $tanggal_kembali_rencana
 * @property string|null $tanggal_pengembalian_aktual
 * @property string|null $titik_temu
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PersonalBook $book
 * @property-read \App\Models\User $borrower
 * @property-read \App\Models\User $owner
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereBookId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereBorrowerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereTanggalKembaliRencana($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereTanggalPengembalianAktual($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereTanggalPinjamRencana($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereTitikTemu($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereUpdatedAt($value)
 */
	class Transaction extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $username
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $kota
 * @property string|null $deskripsi
 * @property string|null $no_telp
 * @property array<array-key, mixed>|null $preferred_genres
 * @property string|null $foto_profil
 * @property int $is_admin
 * @property string $password
 * @property string $role
 * @property int $sp_count
 * @property int $is_banned
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Achievement> $achievements
 * @property-read int|null $achievements_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $blockedBy
 * @property-read int|null $blocked_by_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $blockedUsers
 * @property-read int|null $blocked_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TimelinePost> $bookmarkedPosts
 * @property-read int|null $bookmarked_posts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Bookmark> $bookmarks
 * @property-read int|null $bookmarks_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $borrowedBooks
 * @property-read int|null $borrowed_books_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Follow> $followers
 * @property-read int|null $followers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Follow> $following
 * @property-read int|null $following_count
 * @property-read string|null $avatar_url
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PersonalBook> $personalBooks
 * @property-read int|null $personal_books_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PersonalBook> $readingBooks
 * @property-read int|null $reading_books_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TimelineComment> $timelineComments
 * @property-read int|null $timeline_comments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TimelinePost> $timelinePosts
 * @property-read int|null $timeline_posts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User excludeBlocked()
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDeskripsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFotoProfil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsBanned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereKota($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereNoTelp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePreferredGenres($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereSpCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 */
	class User extends \Eloquent implements \Filament\Models\Contracts\FilamentUser {}
}

