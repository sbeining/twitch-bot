<?php declare(strict_types=1);

namespace TwitchBot\Processor\Module;

use SleekDB\SleekDB;

class PokemonRaidModule extends Module
{
    /** @var SleekDB */
    private $db;

    /**
     * @return void
     */
    public function __construct() {
        $this->db = SleekDB::store('raids', __DIR__ . '/../../databases/');
    }

    /**
     * @param string $in
     *
     * @return string|null
     */
    public function handle(string $in): ?string {
        $data = json_decode($in, true);
        $text = $data['content'];
        $this->user = $data['user'];
        $this->channel = $data['channel'];
        $isBroadcaster = "#{$this->user}" === $this->channel;
        $result = null;

        if (preg_match('/^!raid$/', $text) && ($isBroadcaster || $this->user === 'aiyekara')) {
            $raid = $this->getRaid();

            if (!$raid) {
                $this->createRaid();
                $result['content'] = 'Raid created';
            } else {
                $this->emptyRaid();
                $result['content'] = 'Raid emptied';
            }
        }

        if (preg_match('/^!rj$/', $text)) {
            try {
                $this->addMemberToRaid($this->user);
                $result['content'] = "{$this->user} added to raid";
            } catch (\Exception $e) {
                $result['content'] = $e->getMessage();
            }
        }

        if (preg_match('/^!raiders$/', $text)) {
            $raid = $this->getRaid();

            if ($raid === null) {
                $result['content'] = 'There is no open raid!';
            } else if (empty($raid['members'])) {
                $result['content'] = 'The raid queue is empty';
            } else {
                $result['content'] = sprintf('The full raid queue is: %s', implode(', ', $raid['members']));
            }
        }

        if (preg_match('/^!r([1-3])$/', $text, $matches) && ($isBroadcaster || $this->user === 'aiyekara')) {
            try {
                $count = (int) $matches[1];
                $members = $this->getRaidMembers();

                $count = min($count, count($members));

                switch ($count) {
                    case 1:
                        $member = array_shift($members);
                        $result['content'] = "Next up {$member}";
                        $members[] = $member;
                        break;
                    case 2:
                        $member1 = array_shift($members);
                        $member2 = array_shift($members);
                        $result['content'] = "Next up {$member1} and {$member2}";
                        $members[] = $member1;
                        $members[] = $member2;
                        break;
                    case 3:
                        $member1 = array_shift($members);
                        $member2 = array_shift($members);
                        $member3 = array_shift($members);
                        $result['content'] = "Next up {$member1}, {$member2} and {$member3} ";
                        $members[] = $member1;
                        $members[] = $member2;
                        $members[] = $member3;
                        break;
                }

                $this->setRaidMembers($members);
            } catch (\Exception $e) {
                $result['content'] = $e->getMessage();
            }
        }

        if (preg_match('/^!rj (\w+)$/', $text) && ($isBroadcaster || $this->user === 'aiyekara')) {
            try {
                $username = $matches[1];
                $this->removeMemberFromRaid($this->user);
                $result['content'] = "{$this->user} removed from the raid";
            } catch (\Exception $e) {
                $result['content'] = $e->getMessage();
            }
        }

        return json_encode($result) ?: null;
    }

    private function getRaid(): ?array {
        $raids = $this->db->where('channel', '=', $this->channel)->fetch();

        if (empty($raids)) {
            return null;
        }

        return $raids[0];
    }

    private function createRaid(): void {
        $raid = [
            'channel' => $this->channel,
            'creator' => $this->user,
            'members' => [],
        ];

        $this->db->insert($raid);
    }

    private function emptyRaid(): void {
        if ($this->getRaid() === null) {
            throw new \Exception("There is no open raid!");
        }

        $raid = [
            'channel' => $this->channel,
            'creator' => $this->user,
            'members' => [],
        ];

        $this->db->where('channel', '=', $this->channel)->update($raid);
    }


    private function getRaidMembers(): array {
        $raid = $this->getRaid();

        if (!$raid) {
            throw new \Exception("There is no open raid!");
        }

        $members = $raid['members'];

        if (empty($members)) {
            throw new \Exception("The raid is still empty!");
        }

        return $members;
    }

    private function setRaidMembers(array $members): void {
        if ($this->getRaid() === null) {
            throw new \Exception("There is no open raid!");
        }

        $raid = [
            'channel' => $this->channel,
            'creator' => $this->user,
            'members' => $members,
        ];

        $this->db->where('channel', '=', $this->channel)->update($raid);
    }

    private function addMemberToRaid($member): void {
        $raid = $this->getRaid();

        if (!$raid) {
            throw new \Exception("There is no open raid!");
        }

        if (in_array($member, $raid['members'])) {
            throw new \Exception("{$member} is already in the raid!");
        }

        $raid['members'][] = $member;

        $this->db->where('channel', '=', $this->channel)->update($raid);
    }
}
