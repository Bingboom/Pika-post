<?php 
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

require_once XH_SOCIAL_DIR.'/includes/abstracts/abstract-xh-schema.php';
if(!class_exists('XH_Social_Channel_Wechat_Model_Ext')){
    class XH_Social_Channel_Wechat_Model_Ext extends Abstract_XH_Social_Schema{
        /**
         * {@inheritDoc}
         * @see Abstract_XH_Social_Schema::init()
         */
        public function init(){
            $this->on_version_102();
        }

        public function on_version_102(){
            $collate=$this->get_collate();
            global $wpdb;
            $wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}xh_social_channel_wechat_queue`(
                `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
                `ext_user_id` BIGINT(20) NULL DEFAULT NULL,
                `user_id` BIGINT(20) NULL DEFAULT NULL,
                `ip` varchar(32) NULL DEFAULT NULL,
                `uid` varchar(32) NULL DEFAULT NULL,
                `created_date` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE INDEX `uid_unique` (`uid`)
            )
            $collate;");

            if(!empty($wpdb->last_error)){
                XH_Social_Log::error($wpdb->last_error);
                throw new Exception($wpdb->last_error);
            }
        }
    }
}
?>