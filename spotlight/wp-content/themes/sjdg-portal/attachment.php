<?php
/**
* Redirect attachments template to actual files
*/
wp_redirect( wp_get_attachment_url(), 301 );
