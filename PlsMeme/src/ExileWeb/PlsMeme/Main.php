<?php

declare(strict_types=1);

namespace ExileWeb\PlsMeme;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerChatEvent;
//Import FormAPI
use jojoe77777\FormAPI\SimpleForm;

class Main extends PluginBase implements Listener{
public $cfg;
    public $memes;

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        if ($this->getServer()->getPluginManager()->getPlugin("FormAPI") == null) {
            $this->getLogger()->warning(C::RED . "Please download FormAPI!");
            $this->getServer()->getPluginManager()->disablePlugin($this->getServer()->getPluginManager()->getPlugin("MemeUI"));

        }

    }

    public function sendInfo(Player $player, $i){
        $name = $player->getName();
        $meme = $this->memes[$i];
        $form = new SimpleForm(function (Player $player, $data) {
            $result = $data;
            switch ($result) {
                case "0":

                    break;
            }
        });
        $form->setTitle("Meme UI");
        $form->setContent(C::BOLD . "Name: " . $meme["name"] . ". W: " . $meme["width"] . " H: " . $meme["height"]);
        $form->addButton("Close");
        $form->sendToPlayer($player);
    }

    public function sendMemes(Player $player)
    {
        $name = $player->getName();
        $form = new SimpleForm(function (Player $player, $data) {
            $result = $data;
            switch ($result) {
                default:
                    $this->sendInfo($player, $result);
                    break;
                case "0":

                    break;
            }
        });
        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );
        $limit = 20;
        $memes = file_get_contents("https://api.imgflip.com/get_memes", false, stream_context_create($arrContextOptions));
        if ($memes === FALSE) {
            $this->getLogger()->error("MemeUI could not get any memes :(");
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }
        $responseData = json_decode($memes, TRUE);
        $memes = array_splice($responseData['data']['memes'], $limit);
        $this->memes = $memes;
        shuffle($memes);
        foreach ($memes as $meme) {
            //$meme["url"] = preg_replace("/^https:/i", "http:", $meme["url"]);
            //$meme['url'] = str_replace('\\', '/', $meme['url']);
            $form->addButton($meme['name'], 1, $meme['url']);
        }
        $form->setTitle("Meme UI");
        $form->setContent(C::BOLD . "Meme UI V1.2 ");
        $form->sendToPlayer($player);

    }


    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        $this->sendMemes($player);
    }
}
