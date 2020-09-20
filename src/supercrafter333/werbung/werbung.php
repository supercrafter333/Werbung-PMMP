<?php

namespace supercrafter333\werbung;

use DateInterval;
use DateTime;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\level\sound\AnvilFallSound;
use pocketmine\level\sound\GhastSound;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

/**********************************************************************
 * Copyright© by Christoph Regensburger 2020©
 *
 * This Code is Copyrighted by supercrafter333 (Christoph Regensburger)!
 * Dieser Code darf nicht kopiert, verkauft, verliehen oder als sein
 * eigen ausgegeben werden!
 * This Plugin is Licensed with GNU General Public Licens!
***********************************************************************/

class werbung extends PluginBase implements Listener
{

    public function onEnable()
    {
        $this->getConfig();
        @mkdir($this->getDataFolder());
        $config = new Config($this->getDataFolder()."config.yml", Config::YAML);
        $config->save();
    }

    public function onCommand(CommandSender $s, Command $cmd, string $label, array $args): bool
    {
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        if ($cmd->getName() == "werbung") {
            if ($s instanceof Player) {
                if (count($args) >= 1) {
                    if ($s->hasPermission("werbung.cmd")) {
                        $this->getConfig();
                        $cd = new Config($this->getDataFolder() . "cooldown.yml", Config::YAML);
                        if (!$cd->exists($s->getName())) {
                            $cd->set($s->getName(), date('Y-m-d H:i:s'));
                            $cd->save();
                        }
                        $last = new DateTime($cd->get($s->getName()));
                        if (new DateTime("now") > $last) {
                            $this->getServer()->broadcastMessage(" ");
                            $this->getServer()->broadcastMessage(str_replace(["{player}"], [$s->getName()], $config->get("werbung-text")));
                            $this->getServer()->broadcastMessage(implode(" ", $args));
                            $this->getServer()->broadcastMessage($config->get("werbung-text"));
                            $this->getServer()->broadcastMessage(" ");
                            $date = new DateTime('+'.$config->get("cooldown-minutes").' minutes');
                            $cd->set($s->getName(), $date->format("Y-m-d H:i:s"));
                            $cd->save();
                            foreach ($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
                                $onlinePlayer->getLevel()->addSound(new GhastSound($onlinePlayer));
                                return true;
                            }
                        } else {
                            $s->sendMessage(str_replace(["{player}"], [$s->getName()], $config->get("wait-message")));
                            $s->getLevel()->addSound(new AnvilFallSound($s));
                            return true;
                        }
                    } else {
                        $s->sendMessage(str_replace(["{player}"], [$s->getName()], $config->get("noperm-message")));
                        $s->getLevel()->addSound(new AnvilFallSound($s));
                        return true;
                    }
                } else {
                    $s->sendMessage(str_replace(["{player}"], [$s->getName()], $config->get("no-arguments-message")));
                    $s->getLevel()->addSound(new AnvilFallSound($s));
                    return true;
                }
            }
            $s->sendMessage($config->get("only-In-Game-message"));
            return true;
        }
        return true;
    }
}
