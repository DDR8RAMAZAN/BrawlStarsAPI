<?php
$passlist = json_decode(file_get_contents("env.json")); // Include key file
require_once 'api.php'; // Include API
$api = new BrawlStarsAPI($passlist["BrawlStarsAPIKey"]); // Start APı

$result = null;  // Search results
$searchType = isset($_GET['type']) ? $_GET['type'] : 'player'; // Get search type
$tag = isset($_GET['tag']) ? $_GET['tag'] : ''; // Get player tag

if (!empty($tag)) {
    if ($searchType === 'player') {
        $result = $api->getPlayer($tag);
    } else {
        $result = $api->getClub($tag);
    }
}

// Varsayılan avatar URL'si
$defaultAvatarUrl = "https://cdn.brawlstats.com/player-thumbnails/28000000.png";
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brawl Stars Arama</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Brawl Stars Arama</h1>
            <nav>
                <a href="index.php" class="active">Arama</a>
                <a href="rankings.php">Sıralama</a>
            </nav>
        </header>

        <main>
            <div class="search-container">
                <form action="" method="GET">
                    <div class="search-box">
                        <input type="text" 
                               name="tag" 
                               placeholder="Oyuncu veya Kulüp Tag'i (#ABC123)" 
                               value="<?php echo htmlspecialchars($tag); ?>"
                               required>
                        <select name="type">
                            <option value="player" <?php echo $searchType === 'player' ? 'selected' : ''; ?>>Oyuncu</option>
                            <option value="club" <?php echo $searchType === 'club' ? 'selected' : ''; ?>>Kulüp</option>
                        </select>
                        <button type="submit">Ara</button>
                    </div>
                </form>
            </div>

            <?php if ($result && !isset($result['error'])): ?>
                <div class="result-container">
                    <?php if ($searchType === 'player'): ?>
                        <div class="player-info">
                            <div class="profile-actions">
                                <a href="https://link.brawlstars.com/invite/friend/<?php echo urlencode(ltrim($result['tag'], '#')); ?>" class="action-button" target="_blank">
                                    <i class="fas fa-user-plus"></i> Arkadaş Ekle
                                </a>
                                <button onclick="shareProfile('<?php echo $result['tag']; ?>', '<?php echo $result['name']; ?>')" class="action-button">
                                    <i class="fas fa-share"></i> Paylaş
                                </button>
                            </div>
                            <div class="player-header">
                                <img src="<?php echo isset($result['icon']['id']) ? "https://cdn.brawlstats.com/player-thumbnails/" . $result['icon']['id'] . ".png" : $defaultAvatarUrl; ?>" 
                                     alt="Profil Resmi" 
                                     class="player-avatar"
                                     onerror="this.src='<?php echo $defaultAvatarUrl; ?>'">
                                <div>
                                    <h2 style="color: <?php echo $result['nameColor'] ?? '#ffffff'; ?>">
                                        <?php echo htmlspecialchars($result['name']); ?>
                                    </h2>
                                    <?php if (isset($result['club'])): ?>
                                        <a href="index.php?type=club&tag=<?php echo urlencode($result['club']['tag']); ?>" class="club-link">
                                            <?php echo htmlspecialchars($result['club']['name']); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="stats">
                                <p>Kupa: <?php echo number_format($result['trophies']); ?></p>
                                <p>En Yüksek Kupa: <?php echo number_format($result['highestTrophies']); ?></p>
                                <p>3v3 Zafer: <?php echo number_format($result['3vs3Victories']); ?></p>
                                <p>Solo Zafer: <?php echo number_format($result['soloVictories']); ?></p>
                                <p>Duo Zafer: <?php echo number_format($result['duoVictories']); ?></p>
                            </div>

                            <div class="section-nav">
                                <a href="#brawlers" class="active">Savaşçılar</a>
                                <a href="#battles">Son Savaşlar</a>
                            </div>
                            
                            <div id="brawlers" class="brawlers-container">
                                <h3>Savaşçılar</h3>
                                <div class="brawlers-grid">
                                    <?php foreach ($result['brawlers'] as $brawler): ?>
                                        <div class="brawler-card">
                                            <img src="<?php echo 'https://cdn.brawlstats.com/character-arts/' . $brawler['id'] . '.png'; ?>" 
                                                 alt="<?php echo htmlspecialchars($brawler['name']); ?>"
                                                 class="brawler-bg"
                                                 onerror="this.src='<?php echo $defaultAvatarUrl; ?>'">
                                            <div class="brawler-power">
                                                Güç <?php echo $brawler['power']; ?>
                                            </div>
                                            <div class="brawler-trophies">
                                                <img src="https://brawlstats.com/dist/trophy.96ebb0874d0e7e7a7c235bfbb751f2cf.png" alt="Kupa" class="trophy-icon">
                                                <?php echo number_format($brawler['trophies']); ?>
                                            </div>
                                            <div class="brawler-info">
                                                <h4><?php echo htmlspecialchars($brawler['name']); ?></h4>
                                                <p>En Yüksek Kupa: <?php echo number_format($brawler['highestTrophies']); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div id="battles" class="battles-container">
                                <h3>Son Savaşlar</h3>
                                <?php 
                                $battle_log = $api->getBattleLog($tag);
                                ?>
                                <?php if (isset($battle_log['items']) && !empty($battle_log['items'])): ?>
                                    <div class="battles-grid">
                                    <?php foreach ($battle_log['items'] as $battle): ?>
                                        <div class="battle-card">
                                            <div class="battle-header">
                                                <img src="<?php echo 'https://cdn.brawlstats.com/event-icons/event_mode_' . strtolower(str_replace( ' ', '_', $battle['battle']['mode'])) . '.png'; ?>" 
                                                     alt="<?php echo htmlspecialchars($battle['battle']['mode']); ?>"
                                                     class="battle-mode-icon"
                                                     onerror="this.src='<?php echo $defaultAvatarUrl; ?>'">
                                                <div class="battle-mode-info">
                                                    <div class="battle-mode">
                                                        <?php echo htmlspecialchars($battle['battle']['mode']); ?>
                                                        <?php if (isset($battle['event']['map'])): ?>
                                                            - <?php echo htmlspecialchars($battle['event']['map']); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="battle-time">
                                                        <?php 
                                                        //$battleTime = new DateTime();
                                                        //$battleTime->setTimezone(new DateTimeZone('Europe/Istanbul'));
                                                        echo $battle['battleTime']; //$battleTime->format('d.m.Y H:i'); 
                                                        ?>
                                                    </div>
                                                </div>
                                                <?php if (isset($battle['battle']['duration'])): ?>
                                                    <div class="battle-duration">
                                                        <?php echo floor($battle['battle']['duration'] / 60) . ':' . str_pad($battle['battle']['duration'] % 60, 2, '0', STR_PAD_LEFT); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="battle-result-section">
                                                <div class="battle-result <?php echo strtolower($battle['battle']['result']); ?>">
                                                    <?php 
                                                    $result = strtolower($battle['battle']['result']);
                                                    if ($result === 'victory') echo 'Zafer';
                                                    elseif ($result === 'defeat') echo 'Yenilgi';
                                                    else echo 'Berabere';
                                                    ?>
                                                </div>
                                                <?php if (isset($battle['battle']['trophyChange'])): ?>
                                                    <div class="battle-trophy-change <?php echo $battle['battle']['trophyChange'] > 0 ? 'positive' : 'negative'; ?>">
                                                        <?php echo ($battle['battle']['trophyChange'] > 0 ? '+' : '') . $battle['battle']['trophyChange']; ?> 🏆
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="battle-content">
                                                <div class="battle-teams">
                                                    <?php if (isset($battle['battle']['teams'])): ?>
                                                        <!-- Takım Savaşı -->
                                                        <?php foreach ($battle['battle']['teams'] as $teamIndex => $team): ?>
                                                            <div class="battle-team">
                                                                <div class="battle-team-header">
                                                                    <?php echo $teamIndex === 0 ? '' : 'VS'; ?>
                                                                </div>
                                                                <div class="battle-players">
                                                                    <?php foreach ($team as $player): ?>
                                                                        <div class="battle-player <?php 
                                                                            echo isset($player['tag']) && $player['tag'] === ltrim($tag, '#') ? 'is-me' : '';
                                                                            echo isset($player['starPlayer']) && $player['starPlayer'] ? ' is-mvp' : '';
                                                                        ?>">
                                                                            <img src="<?php echo 'https://cdn.brawlify.com/brawler/' . $player['brawler']['id'] . '.png'; ?>" 
                                                                                 alt="<?php echo htmlspecialchars($player['brawler']['name']); ?>"
                                                                                 class="player-brawler-icon"
                                                                                 onerror="this.src='<?php echo $defaultAvatarUrl; ?>'">
                                                                            <div class="player-info">
                                                                                <div class="player-name">
                                                                                    <?php echo htmlspecialchars($player['name']); ?>
                                                                                </div>
                                                                                <?php if (isset($player['brawler']['trophies'])): ?>
                                                                                <div class="player-trophies">
                                                                                    <?php echo number_format($player['brawler']['trophies']); ?> 🏆
                                                                                </div>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                            <?php if (isset($player['brawler']['power'])): ?>
                                                                            <div class="player-power">
                                                                                Güç <?php echo $player['brawler']['power']; ?>
                                                                            </div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php elseif (isset($battle['battle']['players'])): ?>
                                                        <!-- Solo/Duo Savaşı -->
                                                        <div class="battle-team">
                                                            <div class="battle-team-header">
                                                                Oyuncular
                                                            </div>
                                                            <div class="battle-players">
                                                                <?php foreach ($battle['battle']['players'] as $player): ?>
                                                                    <div class="battle-player <?php 
                                                                        echo isset($player['tag']) && $player['tag'] === ltrim($tag, '#') ? 'is-me' : '';
                                                                        echo isset($player['starPlayer']) && $player['starPlayer'] ? ' is-mvp' : '';
                                                                    ?>">
                                                                        <img src="<?php echo 'https://cdn.brawlify.com/brawler-arts/' . $player['brawler']['id'] . '.png'; ?>" 
                                                                             alt="<?php echo htmlspecialchars($player['brawler']['name']); ?>"
                                                                             class="player-brawler-icon"
                                                                             onerror="this.src='<?php echo $defaultAvatarUrl; ?>'">
                                                                        <div class="player-info">
                                                                            <div class="player-name">
                                                                                <?php echo htmlspecialchars($player['name']); ?>
                                                                            </div>
                                                                            <?php if (isset($player['brawler']['trophies'])): ?>
                                                                            <div class="player-trophies">
                                                                                <?php echo number_format($player['brawler']['trophies']); ?> 🏆
                                                                            </div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                        <?php if (isset($player['brawler']['power'])): ?>
                                                                        <div class="player-power">
                                                                            Güç <?php echo $player['brawler']['power']; ?>
                                                                        </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p>Savaş geçmişi bulunamadı.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="club-info">
                            <div class="profile-actions">
                                <button onclick="shareClub('<?php echo $result['tag']; ?>', '<?php echo $result['name']; ?>')" class="action-button">
                                    <i class="fas fa-share"></i> Paylaş
                                </button>
                            </div>
                            <h2><?php echo htmlspecialchars($result['name']); ?></h2>
                            <div class="stats">
                                <p>Açıklama: <?php echo htmlspecialchars($result['description']); ?></p>
                                <p>Toplam Kupa: <?php echo number_format($result['trophies']); ?></p>
                                <p>Gerekli Kupa: <?php echo number_format($result['requiredTrophies']); ?></p>
                                <p>Üye Sayısı: <?php echo count($result['members']); ?>/100</p>
                            </div>
                            
                            <div class="members-container">
                                <h3>Üyeler</h3>
                                <div class="members-list">
                                    <?php foreach ($result['members'] as $member): ?>
                                        <div class="member-card">
                                            <img src="<?php echo isset($member['avatarUrl']) ? $member['avatarUrl'] : $defaultAvatarUrl; ?>" 
                                                 alt="Üye Avatarı"
                                                 onerror="this.src='<?php echo $defaultAvatarUrl; ?>'">
                                            <div class="member-info">
                                                <h4 style="color: <?php echo $member['nameColor'] ?? '#ffffff'; ?>">
                                                    <?php echo htmlspecialchars($member['name']); ?>
                                                </h4>
                                                <p>Rol: <?php echo ucfirst($member['role']); ?></p>
                                                <p>Kupa: <?php echo number_format($member['trophies']); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php elseif (isset($result['error'])): ?>
                <div class="error-message">
                    <p>Hata: <?php echo htmlspecialchars($result['message']); ?></p>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
    function shareProfile(tag, name) {
        const text = `${name} (#${tag}) Brawl Stars profilimi kontrol et!`;
        const url = `${window.location.origin}${window.location.pathname}?type=player&tag=${encodeURIComponent(tag)}`;
        
        if (navigator.share) {
            navigator.share({
                title: 'Brawl Stars Profili',
                text: text,
                url: url
            });
        } else {
            // Fallback for browsers that don't support Web Share API
            const dummy = document.createElement('textarea');
            document.body.appendChild(dummy);
            dummy.value = `${text}\n${url}`;
            dummy.select();
            document.execCommand('copy');
            document.body.removeChild(dummy);
            alert('Profil bağlantısı panoya kopyalandı!');
        }
    }

    function shareClub(tag, name) {
        const text = `${name} (#${tag}) Brawl Stars kulübünü kontrol et!`;
        const url = `${window.location.origin}${window.location.pathname}?type=club&tag=${encodeURIComponent(tag)}`;
        
        if (navigator.share) {
            navigator.share({
                title: 'Brawl Stars Kulübü',
                text: text,
                url: url
            });
        } else {
            const dummy = document.createElement('textarea');
            document.body.appendChild(dummy);
            dummy.value = `${text}\n${url}`;
            dummy.select();
            document.execCommand('copy');
            document.body.removeChild(dummy);
            alert('Kulüp bağlantısı panoya kopyalandı!');
        }
    }

    // Smooth scroll for section navigation
    document.querySelectorAll('.section-nav a').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            targetElement.scrollIntoView({ behavior: 'smooth' });
            
            // Update active state
            document.querySelectorAll('.section-nav a').forEach(a => a.classList.remove('active'));
            this.classList.add('active');
        });
    });
    </script>
</body>
</html> 