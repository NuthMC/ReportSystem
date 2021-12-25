<?php

namespace nuthmc\report;

use pocketmine\Server;
use pocketmine\player\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\event\Listener;
use pocketmine\utils\TextFormat as TE;

class Loader extends PluginBase implements Listener {
    
    public $players = [];
  
    public function onEnable() {
        @mkdir($this->getDataFolder());
        $this->saveResource("config.yml");
        if($this->getConfig()->get("api")===null) {
          $this->getLogger()->info("unknown api");
          $this->getServer()->getPluginManager()->disablePlugin($this);
        }
    }
    
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args):bool {
        switch($cmd->getName()) {
        case "report": 
            if($sender instanceof Player) {
                $this->reportForm($sender);
            } else {
          $sender->sendMessage("command can extend in-game only");
        }
        break;
    }
    
    return true;
    }
  
    public function reportForm($player) {
        $list = [];
        foreach($this->getServer()->getOnlinePlayers() as $p) {
            $list[] = $p->getName();
        }
        
        $this->players[$player->getName()] = $list;
        
        $form = new CustomForm(function (Player $player, array $data = null){
            if($data === null) {
              $player->sendMessage(TE::RED."Report Failed");
                return true;
            }
            $web=new Webhook($this->getConfig()->get("api"));
            $msg=new Message();
            $e=new Embed();
            $index=$data[1];
            $e->setTitle("Player Report");
            $e->setDescription("{$player->getName()} reported {$this->players[$player->getName()][$index]}  [Reason: {$data[2]}]");
            $msg->addEmbed($e);
            $web->send($msg);
            $player->sendMessage(TE::GREEN."Report was sent");
        });
        $form->setTitle("Reporter");
        $form->addLabel("Report");
        $form->addDropdown("Select a player", $this->players[$player->getName()]);
        $form->addInput("Reason", "Type a reason", "Hacking");
        $form->sendToPlayer($player);
        return $form;
    }
    
}
