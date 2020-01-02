<?php

namespace Javareact\Xinge\Entity;


class MessageIOS
{

    private $m_title;
    private $m_content;
    private $m_expireTime;
    private $m_sendTime;
    private $m_acceptTimes;
    private $m_custom;
    private $m_raw;
    private $m_type;
    private $m_alert;
    private $m_badge;
    private $m_sound;
    private $m_category;
    private $m_loopInterval;
    private $m_loopTimes;
    /** @var int    iOS平台用，必须为0，不区分通知栏消息和静默消息 */
    const TYPE_APNS_NOTIFICATION = 0;
    /** @var int    iOS平台用，必须为0，不区分通知栏消息和静默消息 */
    const TYPE_REMOTE_NOTIFICATION = 0;
    const MAX_LOOP_TASK_DAYS       = 15;

    /**
     * MessageIOS constructor.
     */
    public function __construct()
    {
        $this->m_acceptTimes = array();
        $this->m_type        = self::TYPE_APNS_NOTIFICATION;
    }

    public function __destruct()
    {
    }

    public function setTitle($title)
    {
        $this->m_title = $title;
    }

    public function setContent($content)
    {
        $this->m_content = $content;
    }

    public function setExpireTime($expireTime)
    {
        $this->m_expireTime = $expireTime;
    }

    public function getExpireTime()
    {
        return $this->m_expireTime;
    }

    public function setSendTime($sendTime)
    {
        $this->m_sendTime = $sendTime;
    }

    public function getSendTime()
    {
        return $this->m_sendTime;
    }

    public function addAcceptTime($acceptTime)
    {
        $this->m_acceptTimes[] = $acceptTime;
    }

    public function acceptTimeToJson()
    {
        $ret = array();
        foreach ($this->m_acceptTimes as $acceptTime) {
            $ret[] = $acceptTime->toArray();
        }
        return $ret;
    }

    public function setCustom($custom)
    {
        $this->m_custom = $custom;
    }

    public function setRaw($raw)
    {
        $this->m_raw = $raw;
    }

    public function setAlert($alert)
    {
        $this->m_alert = $alert;
    }

    /**
     * 用户设置角标数字
     * @param int $badge
     * 仅限iOS 平台使用,放在aps字段内<br>
     * 1) -1:角标数字不变<br>
     * 2) -2:角标数字自动加1<br>
     * 3) >=0:设置「自定义」角标数字<br>
     */
    public function setBadge(int $badge)
    {
        $this->m_badge = $badge;
    }

    public function setSound($sound)
    {
        $this->m_sound = $sound;
    }

    public function setType($type)
    {
        $this->m_type = $type;
    }

    public function getType()
    {
        return $this->m_type;
    }

    public function getCategory()
    {
        return $this->m_category;
    }

    public function setCategory($category)
    {
        $this->m_category = $category;
    }

    public function getLoopInterval()
    {
        return $this->m_loopInterval;
    }

    public function setLoopInterval($loopInterval)
    {
        $this->m_loopInterval = $loopInterval;
    }

    public function getLoopTimes()
    {
        return $this->m_loopTimes;
    }

    public function setLoopTimes($loopTimes)
    {
        $this->m_loopTimes = $loopTimes;
    }

    /**
     * 组装发送参数
     * @return string
     */
    public function toJson()
    {
        if (!empty($this->m_raw)) {
            return $this->m_raw;
        }

        $ret                = array();
        $ret['custom']      = $this->m_custom;
        $ret['accept_time'] = $this->acceptTimeToJson();

        $aps = array();
        if ($this->m_type == self::TYPE_APNS_NOTIFICATION) {
            $aps['alert'] = [
                'title'   => $this->m_title,
                'content' => $this->m_content,
            ];
            if (isset($this->m_badge)) {
                $aps['badge_type'] = $this->m_badge;
            }

            if (isset($this->m_sound)) {
                $aps['sound'] = $this->m_sound;
            }

            if (isset($this->m_category)) {
                $aps['category'] = $this->m_category;
            }

        } else if ($this->m_type == self::TYPE_REMOTE_NOTIFICATION) {
            $aps['content-available'] = 1;
        }
        $ret['aps'] = $aps;
        return json_encode($ret);
    }

    public function isValid()
    {
        return true;//暂时不验证
        if (isset($this->m_expireTime)) {
            if (!is_int($this->m_expireTime) || $this->m_expireTime > 3 * 24 * 60 * 60) {
                return false;
            }

        } else {
            $this->m_expireTime = 0;
        }

        if (isset($this->m_sendTime)) {
            if (strtotime($this->m_sendTime) === false) {
                return false;
            }

        } else {
            $this->m_sendTime = "2014-03-13 12:00:00";
        }

        if (!empty($this->m_raw)) {
            if (is_string($this->m_raw)) {
                return true;
            } else {
                return false;
            }

        }
        if (!is_int($this->m_type) || $this->m_type < self::TYPE_APNS_NOTIFICATION || $this->m_type > self::TYPE_REMOTE_NOTIFICATION) {
            return false;
        }

        foreach ($this->m_acceptTimes as $value) {
            if (!($value instanceof TimeInterval) || !$value->isValid()) {
                return false;
            }

        }

        if (isset($this->m_custom)) {
            if (!is_array($this->m_custom)) {
                return false;
            }

        } else {
            $this->m_custom = array();
        }
        if ($this->m_type == self::TYPE_APNS_NOTIFICATION) {
            if (!isset($this->m_alert)) {
                return false;
            }

            if (!is_string($this->m_alert) && !is_array($this->m_alert)) {
                return false;
            }

        }
        if (isset($this->m_badge)) {
            if (!is_int($this->m_badge)) {
                return false;
            }

        }
        if (isset($this->m_sound)) {
            if (!is_string($this->m_sound)) {
                return false;
            }

        }
        if (isset($this->m_loopInterval)) {
            if (!(is_int($this->m_loopInterval) && $this->m_loopInterval > 0)) {
                return false;
            }
        }
        if (isset($this->m_loopTimes)) {
            if (!(is_int($this->m_loopTimes) && $this->m_loopTimes > 0)) {
                return false;
            }
        }
        if (isset($this->m_loopInterval) && isset($this->m_loopTimes)) {
            if (($this->m_loopTimes - 1) * $this->m_loopInterval + 1 > self::MAX_LOOP_TASK_DAYS) {
                return false;
            }
        }

        return true;
    }

}