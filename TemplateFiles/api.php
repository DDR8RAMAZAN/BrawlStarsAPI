<?php
class BrawlStarsAPI {
    private $token;
    private $baseUrl = 'https://api.brawlstars.com/v1';
    private $brawlifyUrl = 'https://cdn.brawlify.com';
    private $regions = [
        'global' => 'Global',
        'TR' => 'Türkiye',
        'EU' => 'Avrupa',
        'US' => 'Amerika',
        'AS' => 'Asya',
        'SA' => 'Güney Amerika',
        'AU' => 'Avustralya',
        'IN' => 'Hindistan',
        'RU' => 'Rusya'
    ];

    public function __construct($token) {
        $this->token = $token;
    }

    private function makeRequest($endpoint) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['error' => true, 'message' => 'API isteği başarısız oldu', 'code' => $httpCode];
        }

        return json_decode($response, true);
    }

    private function getIconUrl($iconId) {
        return $this->brawlifyUrl . "/profile/" . $iconId . ".png";
    }

    private function getBrawlerIconUrl($brawlerId) {
        return $this->brawlifyUrl . "/brawler/" . $brawlerId . ".png";
    }

    public function getPlayer($playerTag) { 
        $playerTag = urlencode(ltrim($playerTag, '#'));
        $player = $this->makeRequest('/players/%23' . $playerTag);
        
        if (!isset($player['error'])) {
            // Brawler detaylarını al
            foreach ($player['brawlers'] as &$brawler) {
                $brawler['iconUrl'] = $this->getBrawlerIconUrl($brawler['id']);
            }
            // Oyuncu avatar URL'si
            if (isset($player['icon']['id'])) {
                $player['avatarUrl'] = $this->getIconUrl($player['icon']['id']);
            }
        }
        
        return $player;
    }

    public function getBattleLog($playerTag) {
        $playerTag = urlencode(ltrim($playerTag, '#'));
        $player = $this->makeRequest('/players/%23' . $playerTag . "/battlelog");
               
        return $player;
    }

    public function getClub($clubTag) {
        $clubTag = urlencode(ltrim($clubTag, '#'));
        $club = $this->makeRequest('/clubs/%23' . $clubTag);
        
        if (!isset($club['error'])) {
            foreach ($club['members'] as &$member) {
                if (isset($member['icon']['id'])) {
                    $member['avatarUrl'] = $this->getIconUrl($member['icon']['id']);
                }
            }
        }
        
        return $club;
    }

    public function getRankings($region = 'global', $type = 'players') {
        $rankings = $this->makeRequest('/rankings/' . $region . '/' . $type);
        
        if (!isset($rankings['error'])) {
            foreach ($rankings['items'] as &$item) {
                if ($type === 'players' && isset($item['icon']['id'])) {
                    $item['avatarUrl'] = $this->getIconUrl($item['icon']['id']);
                }
            }
        }
        
        return $rankings;
    }

    public function getRegions() {
        return $this->regions;
    }
} 