<!-- template content product -->
<header>
    <hgroup>
        <h2><?php the_title(); ?></h2>
    </hgroup>
</header>

<?php
    $sales_email = 'info@pressform.com.au';
?>

<div class="entry clearfix">
    <a class="email_link" href="mailto:<?php print $sales_email; ?>?subject=Enquiry: <?php the_title(); ?>">
        <img class="mail_icon" src="<?php bloginfo('template_url'); ?>/images/mail_send.png" />  Enquire about this
    </a>
   <!-- 
    <a class="email_link" href="mailto:<?php print $sales_email; ?>?subject=Purchase: <?php the_title(); ?>">
        <img class="mail_icon" src="<?php bloginfo('template_url'); ?>/images/mail_send.png" />  Buy this
    </a>
   -->
    <a class="email_link" href="mailto:?subject=<?php the_title(); ?>&body=<?php the_permalink(); ?>">
        <img class="mail_icon" src="<?php bloginfo('template_url'); ?>/images/mail_send.png" />  Share this page
    </a>
 
    <div class="description">
        <?php the_content(); ?>
    </div>
    <?php
        $downloads = _get_attachments($post_id, $mimetype=null, $not_mimetype=array('image', 'video', 'audio'));
        if( count($downloads) ):
    ?>
        <h3 class="download_label">Downloads:</h3>
        <ul class="attachments attachments-downloads">
            <?php
            is_listDownloads();
            ?>
        </ul>
    <?php endif; ?>


</div>


