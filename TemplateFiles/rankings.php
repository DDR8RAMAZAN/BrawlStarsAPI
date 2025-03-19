<?php
$passlist = json_decode(file_get_contents("env.json"), true);
require_once 'api.php';
$api = new BrawlStarsAPI($passlist["BrawlStarsAPIKey"]);

$type = isset($_GET['type']) ? $_GET['type'] : 'players';
$region = isset($_GET['region']) ? $_GET['region'] : 'global';
$regions = $api->getRegions();

$rankings = $api->getRankings($region, $type);

// Varsayılan avatar URL'si
$defaultAvatarUrl = "https://cdn.brawlify.com/profile/28000000.png";
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brawl Stars Sıralama</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Brawl Stars Sıralama</h1>
            <nav>
                <a href="index.php">Arama</a>
                <a href="rankings.php" class="active">Sıralama</a>
            </nav>
        </header>

        <main>
            <div class="filters">
                <form action="" method="GET">
                    <select name="type" onchange="this.form.submit()">
                        <option value="players" <?php echo $type === 'players' ? 'selected' : ''; ?>>Oyuncular</option>
                        <option value="clubs" <?php echo $type === 'clubs' ? 'selected' : ''; ?>>Kulüpler</option>
                    </select>
                    <select name="region" onchange="this.form.submit()">
                        <?php foreach ($regions as $code => $name): ?>
                            <option value="<?php echo $code; ?>" <?php echo $region === $code ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <?php if ($rankings && !isset($rankings['error'])): ?>
                <div class="rankings-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Sıra</th>
                                <th>Avatar</th>
                                <th>İsim</th>
                                <th>Kupa</th>
                                <?php if ($type === 'clubs'): ?>
                                    <th>Üye Sayısı</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rankings['items'] as $rank => $item): ?>
                                <tr class="ranking-row" onclick="window.location.href='index.php?type=<?php echo $type === 'players' ? 'player' : 'club'; ?>&tag=<?php echo urlencode($item['tag']); ?>'">
                                    <td><?php echo $rank + 1; ?></td>
                                    <td>
                                        <?php if ($type === 'players'): ?>
                                            <img src="<?php echo isset($item['icon']['id']) ? "https://cdn.brawlstats.com/player-thumbnails/" . $item['icon']['id'] . ".png" : $defaultAvatarUrl; ?>" 
                                                 alt="Avatar" 
                                                 class="ranking-avatar"
                                                 onerror="this.src='<?php echo $defaultAvatarUrl; ?>'">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="color: <?php echo $item['nameColor'] ?? '#ffffff'; ?>">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($item['trophies']); ?></td>
                                    <?php if ($type === 'clubs'): ?>
                                        <td><?php echo $item['memberCount']; ?>/100</td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif (isset($rankings['error'])): ?>
                <div class="error-message">
                    <p>Hata: <?php echo htmlspecialchars($rankings['message']); ?></p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html> 