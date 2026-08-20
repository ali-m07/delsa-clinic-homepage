<?php
/**
 * WPCode Snippet — فعال‌سازی ویرایشگر محتوا برای مشاوران
 * نوع: PHP Snippet
 * محل: Run Everywhere
 *
 * بدون این، پست نوع delsa_consultant فقط فیلدهای کوتاه دارد
 * و جایی برای متن کامل پروفایل (HTML) نیست.
 */

if (!defined('ABSPATH')) {
  exit;
}

add_action('init', static function () {
  add_post_type_support('delsa_consultant', 'editor');
}, 20);

add_filter('use_block_editor_for_post_type', static function ($use, $post_type) {
  if ($post_type === 'delsa_consultant') {
    // ویرایشگر کلاسیک برای پیست HTML راحت‌تر است
    return false;
  }
  return $use;
}, 10, 2);
