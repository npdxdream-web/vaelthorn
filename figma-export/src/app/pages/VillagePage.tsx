import { useParams, Link } from "react-router";
import { MessageSquare, Clock, Tag } from "lucide-react";
import { CharacterModule } from "../components/CharacterModule";
import { threads, cities } from "../data/mockData";
import { Button } from "../components/ui/button";

export function VillagePage() {
  const { villageId } = useParams();
  
  // Find village and city info
  let villageName = "Village";
  let cityInfo = cities[0];
  
  for (const city of cities) {
    const village = city.villages.find(v => v.id === villageId);
    if (village) {
      villageName = village.name;
      cityInfo = city;
      break;
    }
  }

  return (
    <div className="mx-auto max-w-7xl px-6 py-8">
      <div className="grid grid-cols-1 gap-8 lg:grid-cols-3">
        {/* Left: Thread List */}
        <div className="lg:col-span-2">
          {/* Header */}
          <div className="mb-6">
            <div className="mb-2 flex items-center gap-2 text-sm text-[#a8a6a3]">
              <Link to="/" className="hover:text-[#D4AF37]">Thiran</Link>
              <span>/</span>
              <span style={{ color: cityInfo.color }}>{cityInfo.name}</span>
              <span>/</span>
              <span className="text-[#e8e6e3]">{villageName}</span>
            </div>
            <h1 className="font-display mb-4 text-3xl tracking-wide text-[#D4AF37]">
              {villageName}
            </h1>
            <div className="flex items-center gap-3">
              <Button className="bg-[#D4AF37] text-[#0f0f0f] hover:bg-[#B8941F]">
                Start New Tale
              </Button>
              <Button variant="outline" className="border-[#2a2a2a] text-[#e8e6e3] hover:border-[#D4AF37] hover:bg-transparent">
                View Archive
              </Button>
            </div>
          </div>

          {/* Thread List */}
          <div className="space-y-4">
            {threads.map((thread) => (
              <Link 
                key={thread.id}
                to={`/thread/${thread.id}`}
                className="group block overflow-hidden rounded-xl border border-[#2a2a2a] bg-[#1a1a1a] transition-all hover:border-[#D4AF37]"
              >
                <div className="p-5">
                  <div className="mb-3 flex items-start justify-between gap-4">
                    <div className="flex-1">
                      <h2 className="mb-1 font-medium text-[#e8e6e3] group-hover:text-[#D4AF37]">
                        {thread.title}
                      </h2>
                      <div className="flex items-center gap-2 text-sm text-[#a8a6a3]">
                        <span>by {thread.author.name}</span>
                        <span>•</span>
                        <span>{thread.village}</span>
                      </div>
                    </div>
                    <div className="flex flex-col items-end gap-1">
                      <div className="flex items-center gap-1 text-sm text-[#a8a6a3]">
                        <Clock className="h-4 w-4" />
                        <span>{thread.lastPost}</span>
                      </div>
                    </div>
                  </div>

                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                      {thread.tags.map((tag) => (
                        <span 
                          key={tag}
                          className="flex items-center gap-1 rounded-full border border-[#2a2a2a] bg-[#141414] px-3 py-1 text-xs text-[#a8a6a3]"
                        >
                          <Tag className="h-3 w-3" />
                          {tag}
                        </span>
                      ))}
                    </div>
                    <div className="flex items-center gap-1 text-sm text-[#B87333]">
                      <MessageSquare className="h-4 w-4" />
                      <span>{thread.replies}</span>
                    </div>
                  </div>
                </div>

                {/* Author Preview */}
                <div 
                  className="border-t px-5 py-3"
                  style={{ 
                    borderColor: thread.author.cityColor + '40',
                    backgroundColor: thread.author.cityColor + '08'
                  }}
                >
                  <div className="flex items-center gap-3">
                    <div 
                      className="flex h-8 w-8 items-center justify-center rounded-full border-2"
                      style={{ 
                        borderColor: thread.author.cityColor,
                        background: `linear-gradient(135deg, ${thread.author.cityColor}aa, ${thread.author.cityColor}66)`
                      }}
                    >
                      <span className="text-xs text-[#e8e6e3]">{thread.author.name[0]}</span>
                    </div>
                    <div className="flex items-center gap-2 text-xs text-[#a8a6a3]">
                      <span className="text-[#e8e6e3]">{thread.author.name}</span>
                      <span>•</span>
                      <span>{thread.author.class}</span>
                      <span>•</span>
                      <span>Lv. {thread.author.level}</span>
                    </div>
                  </div>
                </div>
              </Link>
            ))}
          </div>
        </div>

        {/* Right: Character Sidebar */}
        <div className="lg:col-span-1">
          <CharacterModule />

          {/* Village Info */}
          <div className="mt-6 rounded-xl border border-[#2a2a2a] bg-[#1a1a1a] p-6">
            <h3 className="font-display mb-4 text-lg text-[#D4AF37]">Village Info</h3>
            <div className="space-y-3 text-sm">
              <div className="flex justify-between">
                <span className="text-[#a8a6a3]">Active Tales</span>
                <span className="text-[#e8e6e3]">12</span>
              </div>
              <div className="flex justify-between">
                <span className="text-[#a8a6a3]">Members</span>
                <span className="text-[#e8e6e3]">247</span>
              </div>
              <div className="flex justify-between">
                <span className="text-[#a8a6a3]">Founded</span>
                <span className="text-[#e8e6e3]">Age of Iron</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
