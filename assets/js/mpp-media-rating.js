/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
jQuery(function ($) {

    url = MPP_RATING.ajax_url, _nonce = MPP_RATING._nonce;

    $('.mpp-media-rating').rateit({resetable: false});

    $('.mpp-media-rating').bind('rated', function (event, value) {

        var $this = $(this),
            media_id = $this.attr('data-media-id');

        $this.rateit('readonly', true);

        if (!MPP_RATING.is_user_logged_in && mpp_media_rating_exists(media_id)) {
            console.log("Already rated! media");
            return false;
        }

        var data = {
            action: 'vote_me',
            media_id: media_id,
            _nonce: _nonce,
            vote: value
        };

        $.post(url, data, function (resp) {

            if (resp.type == 'error') {
                console.log(resp.message);
            } else if (resp.type == 'success') {
                console.log(resp.message);
                $($this).rateit('value', resp.message.average_vote);
                mpp_media_rating_store(media_id);
            }

        }, 'json');

    });

});

function mpp_media_rating_get_rated_medias() {

    var media_ids = jQuery.cookie('mpp_media_rated_medias') ? jQuery.cookie('mpp_media_rated_medias').split(',').map(function (i) {
        return parseInt(i, 10)
    }) : [];

    return media_ids;

}

function mpp_media_rating_exists(media_id) {

    var media_ids = mpp_media_rating_get_rated_medias();

    if (jQuery.inArray(parseInt(media_id, 10), media_ids) == -1) {
        return false;
    }

    return true;

}

function mpp_media_rating_store(media_id) {

    if (mpp_media_rating_exists(media_id)) {
        return false;
    }

    //alerady existing?
    var media_ids = mpp_media_rating_get_rated_medias();

    media_ids.push(media_id);

    jQuery.cookie('mpp_media_rated_medias', media_ids.join(','), {expires: 1});

}
