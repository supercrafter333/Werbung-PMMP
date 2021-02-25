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
use onebone\economyapi\EconomyAPI;

class werbung extends PluginBase implements Listener
{

    public function onEnable()
    {
        $this->getConfig();
        @mkdir($this->getDataFolder());
        $config = new Config($this->getDataFolder()."config.yml", Config::YAML);
        if ($config->exists("version") && $config->get("version") == "1.5.0") {
            return;
        } else {
            $this->getLogger()->error("Your configuration file [config.yml] is outdated! Please delete the file and restart your server to update the configuration file!");
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }
        $this->saveResource("config.yml");
        if ($config->get("pay-money-to-publish") == "true") {
            $economyapi = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
            if ($economyapi == null) {
                $this->getServer()->getPluginManager()->disablePlugin($this);
            }
        }
    }

    public function onCommand(CommandSender $s, Command $cmd, string $label, array $args): bool
    {
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        if ($cmd->getName() == "werbung") {
            if ($s instanceof Player) {
                if (count($args) >= 1) {
                    if ($s->hasPermission("werbung-pmmp.adversiting.cmd")) {
                        $this->getConfig();
                        $cd = new Config($this->getDataFolder() . "cooldown.yml", Config::YAML);
                        if (!$cd->exists($s->getName())) {
                            $cd->set($s->getName(), date('Y-m-d H:i:s'));
                            $cd->save();
                        }
                        $eco = EconomyAPI::getInstance();
                        $mymoney = $eco->myMoney($s->getName());
                        $last = new DateTime($cd->get($s->getName()));
                        if (new DateTime("now") > $last) {
                            if ($config->get("pay-money-to-publish") == "true") {
                                $betrag = $config->get("money");
                                if (!$s->hasPermission("werbung-pmmp.advertising.nopay")) {
                                    if ($betrag > $mymoney) {
                                        $s->sendMessage($config->get("not-enought-money-message"));
                                    } else {
                                        $eco->reduceMoney($s->getName(), $betrag);
                                        $this->getServer()->broadcastMessage(" ");
                                        $this->getServer()->broadcastMessage(str_replace(["{player}"], [$s->getName()], $config->get("werbung-text")));
                                        $this->getServer()->broadcastMessage(implode(" ", $args));
                                        $this->getServer()->broadcastMessage(str_replace(["{player}"], [$s->getName()], $config->get("werbung-text")));
                                        $this->getServer()->broadcastMessage(" ");
                                        $date = new DateTime('+' . $config->get("cooldown-minutes") . ' minutes');
                                        $cd->set($s->getName(), $date->format("Y-m-d H:i:s"));
                                        $cd->save();
                                        foreach ($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
                                            $onlinePlayer->getLevel()->addSound(new GhastSound($onlinePlayer));
                                            return true;
                                        }
                                    }
                                } else {
                                    $this->getServer()->broadcastMessage(" ");
                                    $this->getServer()->broadcastMessage(str_replace(["{player}"], [$s->getName()], $config->get("werbung-text")));
                                    $this->getServer()->broadcastMessage(implode(" ", $args));
                                    $this->getServer()->broadcastMessage(str_replace(["{player}"], [$s->getName()], $config->get("werbung-text")));
                                    $this->getServer()->broadcastMessage(" ");
                                    $date = new DateTime('+' . $config->get("cooldown-minutes") . ' minutes');
                                    $cd->set($s->getName(), $date->format("Y-m-d H:i:s"));
                                    $cd->save();
                                    foreach ($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
                                        $onlinePlayer->getLevel()->addSound(new GhastSound($onlinePlayer));
                                        return true;
                                    }
                                }
                            } else {
                                $this->getServer()->broadcastMessage(" ");
                                $this->getServer()->broadcastMessage(str_replace(["{player}"], [$s->getName()], $config->get("werbung-text")));
                                $this->getServer()->broadcastMessage(implode(" ", $args));
                                $this->getServer()->broadcastMessage(str_replace(["{player}"], [$s->getName()], $config->get("werbung-text")));
                                $this->getServer()->broadcastMessage(" ");
                                $date = new DateTime('+' . $config->get("cooldown-minutes") . ' minutes');
                                $cd->set($s->getName(), $date->format("Y-m-d H:i:s"));
                                $cd->save();
                                foreach ($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
                                    $onlinePlayer->getLevel()->addSound(new GhastSound($onlinePlayer));
                                    return true;
                                }
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
