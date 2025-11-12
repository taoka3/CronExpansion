# Cronの拡張
### 使用方法 
1. 日時や曜日、コマンドをcrontab.jsonに設定します
1. クロンからCronExpansion.phpを呼び出します

※クロンから再分割してクロンを実行することが出来ます.レンタルサーバーなどは規約違反になる可能性があるので確認が必要です

```php:CronExpansion.php
<?php
class CronExpansion
{
    private $filepath = 'crontab.json';
    private $cronTabs = null;

    public function __construct()
    {
        $fileData = file_get_contents($this->filepath);
        $cronTabData = json_decode($fileData);
        $this->cronTabs =  !json_last_error() ? (object)$cronTabData : (object)[];
        return $this;
    }

    public function run(): object
    {
        $datetime = new DateTime();
        $dateData = explode(',', $datetime->format('m,d,H,i,w'));
        foreach ($this->cronTabs as $cronTab) {
            $i = 0;
            $flg = true;
            $command = null;
            $cronTabDatas = get_object_vars($cronTab);
            if (count($cronTabDatas) === 6) {
                foreach ($cronTabDatas as $key => $val) {
                    if ($flg) {
                        switch ($key) {
                            case 'command':
                                $command =  $val;
                                break;
                            case 'w':
                                if (!(int)$val[(int)$dateData[$i]]) {
                                    $flg = false;
                                    continue;
                                }
                                break;
                            default:
                                if (preg_match('/^\*\/[0-9]{1,2}$/u', $val)) {
                                    if (((int)$dateData[$i] % ((int)str_replace('*/', '', $val))) > 0) {
                                        $flg = false;
                                        continue;
                                    }
                                } elseif (preg_match('/^\*$/u', $val)) {
                                } elseif ((int)$dateData[$i] !== (int)$val) {
                                    $flg = false;
                                    continue;
                                }
                                break;
                        }
                    }
                    $i++;
                }
                if ($flg) {
                    //echo $command;
                    exec($command . " > /dev/null &");
                }
            }
        }
        return $this;
    }
}
(new CronExpansion)->run();
```

```json:crontab.json
[
    {
        "m":"*",
        "d":"*",
        "H":"*",
        "i":"0",
        "w":[1,1,1,1,1,1,1],
        "command":"cd /home/user/www/taoka-toshiaki.com/ && /usr/local/bin/php index.php"
    },
    {
        "m":"*",
        "d":"*",
        "H":"*/5",
        "i":"0",
        "w":[1,1,1,1,1,1,1],
        "command":"cd /home/user/www/taoka-toshiaki.com/x/v2/ && /usr/local/bin/php xPost.php 'taoka'"
    }
]
```
