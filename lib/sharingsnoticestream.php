<?php

class RawSharingsNoticeStream extends NoticeStream
{

    function __construct()
    {

    }

    function getNoticeIds($offset, $limit, $since_id, $max_id)
    {
        $notice = new Notice();
        $qry = null;

        $qry =  'SELECT notice.* FROM notice ';
        $qry .= 'WHERE notice.object_type = "' . SharingsPlugin::SHARINGS_OBJECT . '" ';
        $qry .= 'AND notice.is_local != ' . Notice::GATEWAY . ' ';

        if ($since_id != 0) {
            $qry .= 'AND notice.id > ' . $since_id . ' ';
        }

        if ($max_id != 0) {
            $qry .= 'AND notice.id <= ' . $max_id . ' ';
        }

        // NOTE: we sort by bookmark time, not by notice time!
        $qry .= 'ORDER BY created DESC ';
        if (!is_null($offset)) {
            $qry .= "LIMIT $limit OFFSET $offset";
        }

        $notice->query($qry);
        $ids = array();
        while ($notice->fetch()) {
            $ids[] = $notice->id;
        }

        $notice->free();
        unset($notice);
        return $ids;
    }
}

/**
 * Notice stream for bookmarks
 *
 * @category  Stream
 * @package   StatusNet
 * @author    Stephane Berube <chimo@chromic.org>
 * @copyright 2011 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html AGPL 3.0
 * @link      http://status.net/
 */

class SharingsNoticeStream extends ScopingNoticeStream
{
    function __construct($profile = -1)
    {
        $stream = new RawSharingsNoticeStream();

        $key = 'sharings';

        $profile = Profile::current();

        parent::__construct(new CachingNoticeStream($stream, $key),
                            $profile);
    }
}
