<?php
date_default_timezone_set('Asia/Tokyo');

class CronExpansion
{
    private string $filepath = 'crontab.json';
    private object $cronTabs;

    public function __construct()
    {
        $fileData = file_get_contents($this->filepath);
        $cronTabData = json_decode($fileData);
        $this->cronTabs = !json_last_error() ? (object)$cronTabData : (object)[];
    }

    public function run(): object
    {
        $datetime = new DateTime();
        $dateData = explode(',', $datetime->format('m,d,H,i,w'));

        foreach ($this->cronTabs as $cronTab) {

            $command = null;
            $flg = true;
            $i = 0;

            $cronTabData = get_object_vars($cronTab);

            foreach ($cronTabData as $key => $val) {

                if ($key === 'command') {
                    $command = $val;
                    continue;
                }

                if ($key === 'w') {
                    if (!(int)$val[(int)$dateData[$i]]) {
                        $flg = false;
                        break;
                    }
                } else {
                    if (!$this->cronMatch((int)$dateData[$i], $val)) {
                        $flg = false;
                        break;
                    }
                }

                $i++;
            }

            if ($flg && $command) {
                exec($command . " > /dev/null 2>&1 &");
            }
        }

        return $this;
    }

    private function cronMatch(int $now, string $expr): bool
    {
        $parts = explode(',', $expr);

        foreach ($parts as $part) {

            $part = trim($part);

            // *
            if ($part === '*') {
                return true;
            }

            // */5
            if (preg_match('/^\*\/(\d+)$/', $part, $m)) {
                if ($now % (int)$m[1] === 0) {
                    return true;
                }
            }

            // 1-5
            if (preg_match('/^(\d+)-(\d+)$/', $part, $m)) {
                if ($now >= (int)$m[1] && $now <= (int)$m[2]) {
                    return true;
                }
            }

            // 1-5/2
            if (preg_match('/^(\d+)-(\d+)\/(\d+)$/', $part, $m)) {
                for ($i = $m[1]; $i <= $m[2]; $i += $m[3]) {
                    if ($now === (int)$i) {
                        return true;
                    }
                }
            }

            // single number
            if ((int)$part === $now) {
                return true;
            }
        }

        return false;
    }
}

(new CronExpansion)->run();
