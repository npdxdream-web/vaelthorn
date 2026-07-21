export const kingdoms = [
  {
    id: "ironveil",
    name: "Ironveil",
    color: "#7a8c9e",
    icon: "⚔️",
    description: "The steel-clad fortress of the north, where blacksmiths forge legends.",
    cities: [
      { id: "forgeheart", name: "Forgeheart Square" },
      { id: "steelwatch", name: "The Steelwatch" },
      { id: "anvil-halls", name: "Anvil Halls" },
    ],
  },
  {
    id: "embercrest",
    name: "Embercrest",
    color: "#d94e3f",
    icon: "🔥",
    description: "A volcanic city where fire mages study ancient flames.",
    cities: [
      { id: "ashvale", name: "Ashvale Commons" },
      { id: "pyrestone", name: "Pyrestone District" },
      { id: "cinder-keep", name: "Cinder Keep" },
    ],
  },
  {
    id: "silversong",
    name: "Silversong",
    color: "#c9d4e5",
    icon: "🌙",
    description: "The luminous coastal haven where moonlight guides the way.",
    cities: [
      { id: "tidehaven", name: "Tidehaven Port" },
      { id: "lunar-grove", name: "Lunar Grove" },
      { id: "pearl-market", name: "Pearl Market" },
    ],
  },
  {
    id: "thornhaven",
    name: "Thornhaven",
    color: "#5a7c4e",
    icon: "🌿",
    description: "Deep forest sanctuary protected by ancient druids.",
    cities: [
      { id: "verdant-hollow", name: "Verdant Hollow" },
      { id: "root-chapel", name: "Root Chapel" },
      { id: "wildwood", name: "Wildwood Enclave" },
    ],
  },
];

export const inventoryItems = [
  { id: 1, name: "Runic Blade", icon: "⚔️", type: "weapon", rarity: "uncommon", quantity: 1 },
  { id: 2, name: "Iron Shield", icon: "🛡️", type: "armor", rarity: "common", quantity: 1 },
  { id: 3, name: "Health Potion", icon: "🧪", type: "consumable", rarity: "common", quantity: 5 },
  { id: 4, name: "Mana Crystal", icon: "💠", type: "material", rarity: "uncommon", quantity: 3 },
  { id: 5, name: "Storm Cloak", icon: "🌀", type: "armor", rarity: "rare", quantity: 1 },
];

export const characters = {
  aelric: {
    id: "aelric",
    name: "Aelric Stormborne",
    kingdom: "Ironveil",
    kingdomName: "Ironveil",
    kingdomColor: "#7a8c9e",
    location: "Forgeheart Square",
    rank: "Veteran",
    role: "Battle Mage",
    level: 23,
    class: "Battle Mage",
    race: "Human",
    posts: 147,
    joined: "March 2025",
    notificationCount: 3,
    inventory: [1, 2, 3, 4, 5],
    stats: {
      strength: 18,
      agility: 14,
      intelligence: 16,
      hp: 100,
      mana: 75,
    },
    bio: "A veteran warrior from the northern campaigns, now seeking redemption in Ironveil's forge districts.",
  },
  lyra: {
    id: "lyra",
    name: "Lyra Moonshadow",
    kingdom: "Silversong",
    kingdomName: "Silversong",
    kingdomColor: "#c9d4e5",
    location: "Lunar Grove",
    rank: "Legend",
    role: "Moon Priestess",
    level: 19,
    class: "Moon Priestess",
    race: "Elf",
    posts: 203,
    joined: "January 2025",
    notificationCount: 0,
    inventory: [3, 4],
    stats: {
      strength: 10,
      agility: 15,
      intelligence: 19,
      hp: 80,
      mana: 120,
    },
    bio: "Keeper of lunar secrets and protector of the coastal sanctuaries.",
  },
  kael: {
    id: "kael",
    name: "Kael Emberhart",
    kingdom: "Embercrest",
    kingdomName: "Embercrest",
    kingdomColor: "#d94e3f",
    location: "Pyrestone District",
    rank: "Traveler",
    role: "Fire Conjurer",
    level: 21,
    class: "Fire Conjurer",
    race: "Tiefling",
    posts: 89,
    joined: "April 2025",
    notificationCount: 7,
    inventory: [1, 3],
    stats: {
      strength: 12,
      agility: 11,
      intelligence: 21,
      hp: 70,
      mana: 100,
    },
    bio: "Student of the ancient flame arts, seeking to master the eternal fire.",
  },
};

export const threads = [
  {
    id: "1",
    title: "The Lost Grimoire of Ashwyn",
    city: "Forgeheart Square",
    kingdom: "Ironveil",
    author: characters.aelric,
    replies: 24,
    lastPost: "2 hours ago",
    tags: ["Quest", "Artifact"],
  },
  {
    id: "2",
    title: "Market Day at the Docks",
    city: "Tidehaven Port",
    kingdom: "Silversong",
    author: characters.lyra,
    replies: 67,
    lastPost: "1 day ago",
    tags: ["Social", "Trade"],
  },
  {
    id: "3",
    title: "Forging the Soulblade",
    city: "Anvil Halls",
    kingdom: "Ironveil",
    author: characters.aelric,
    replies: 41,
    lastPost: "3 hours ago",
    tags: ["Crafting", "Weapon"],
  },
  {
    id: "4",
    title: "The Ember Ritual Begins",
    city: "Pyrestone District",
    kingdom: "Embercrest",
    author: characters.kael,
    replies: 15,
    lastPost: "5 hours ago",
    tags: ["Magic", "Ritual"],
  },
  {
    id: "5",
    title: "Shadows in the Wildwood",
    city: "Wildwood Enclave",
    kingdom: "Thornhaven",
    author: characters.lyra,
    replies: 33,
    lastPost: "8 hours ago",
    tags: ["Mystery", "Exploration"],
  },
];

export const posts = [
  {
    id: "1",
    threadId: "1",
    author: characters.aelric,
    timestamp: "June 15, 2026 at 2:30 PM",
    content: `*The heavy oak door of the library creaks as Aelric pushes it open, dust motes dancing in the shaft of light that cuts through the gloom. His armor still bears the marks of yesterday's skirmish.*

"The scholars spoke of a grimoire hidden in these archives—one that hasn't seen daylight in three centuries. If the legends are true, it contains the binding spells used to seal the Shadowrift."

*He runs a gauntleted hand along the spine of ancient tomes, their leather covers cracked with age.*

"Anyone care to help me search? I suspect we're not the only ones looking for it."`,
  },
  {
    id: "2",
    threadId: "1",
    author: characters.lyra,
    timestamp: "June 15, 2026 at 4:15 PM",
    content: `*Lyra steps from the shadows, her moonlit robes shimmering faintly in the dim library. She carries a silver lantern that casts a gentle, cold light.*

"Aelric. I thought I might find you here."

*She approaches the shelves with practiced grace, her fingers trailing along the ancient bindings.*

"The Shadowrift seal... I've studied references to it in the Lunar Archives. The grimoire you seek was penned by Archmagus Valthren—a master of void magic. But be warned, such knowledge comes with a price."

*She turns to face him, her eyes reflecting the lantern's glow.*

"What makes you think the Rift is weakening?"`,
  },
  {
    id: "3",
    threadId: "1",
    author: characters.aelric,
    timestamp: "June 15, 2026 at 5:02 PM",
    content: `*Aelric pauses his search, meeting Lyra's gaze with a grim expression.*

"Three nights ago, I was on patrol near the old fortress ruins. I felt it—a pulse of dark energy, like a heartbeat from beyond the veil. The sentries reported seeing shadows moving against the wind, shapes that had no source."

*He pulls a worn parchment from his satchel and unfolds it on a nearby reading table.*

"This is a map from the War of Embers. See these markings? They indicate where the Rift was sealed. According to the guard captain, there have been disturbances at every single point."

*His voice drops lower.*

"If the seal fails, Lyra... we won't be ready. Not this time."`,
  },
];
