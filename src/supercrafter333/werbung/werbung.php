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
                            $this->getServer()->broadcastMessage("§l├------------------------§b»§cWERBUNG§b«------------------------┤");
                            $this->getServer()->broadcastMessage(implode(" ", $args));
                            $this->getServer()->broadcastMessage("§l├------------------------§b»§cWERBUNG§b«------------------------┤");
                            $this->getServer()->broadcastMessage(" ");
                            $date = new DateTime('+'.$config->get("cooldown-minutes").' minutes');
                            $cd->set($s->getName(), $date->format("Y-m-d H:i:s"));
                            $cd->save();
                            foreach ($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
                                $onlinePlayer->getLevel()->addSound(new GhastSound($onlinePlayer));
                                return true;
                            }
                        } else {
                            $s->sendMessage("§l§cDu musst warten, bis du diesen Befehl noch mal verwenden kannst!");
                            $s->getLevel()->addSound(new AnvilFallSound($s));
                            return true;
                        }
                    } else {
                        $s->sendMessage("Du bist dazu nicht berechtigt!");
                        $s->getLevel()->addSound(new AnvilFallSound($s));
                        return true;
                    }
                } else {
                    $s->sendMessage("Du musst einen Text eingeben!");
                    $s->getLevel()->addSound(new AnvilFallSound($s));
                    return true;
                }
            }
            $s->sendMessage("Nur In-Game verfügbar!");
            return true;
        }
        return true;
    }
}