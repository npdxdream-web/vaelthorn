import { useParams, Link } from "react-router";
import { Send, ArrowLeft } from "lucide-react";
import { CharacterModule } from "../components/CharacterModule";
import { threads, posts } from "../data/mockData";
import { Button } from "../components/ui/button";
import { Textarea } from "../components/ui/textarea";
import { ImageWithFallback } from "../components/figma/ImageWithFallback";

export function ThreadPage() {
  const { threadId } = useParams();
  const thread = threads.find(t => t.id === threadId) || threads[0];
  const threadPosts = posts.filter(p => p.threadId === threadId);

  return (
    <div className="mx-auto max-w-7xl px-6 py-8">
      <div className="grid grid-cols-1 gap-8 lg:grid-cols-3">
        {/* Left: Thread Posts */}
        <div className="lg:col-span-2">
          {/* Header */}
          <div className="mb-6">
            <Link 
              to={`/village/${thread.village.toLowerCase().replace(/\s+/g, '-')}`}
              className="mb-4 inline-flex items-center gap-2 text-sm text-[#a8a6a3] hover:text-[#D4AF37]"
            >
              <ArrowLeft className="h-4 w-4" />
              Back to {thread.village}
            </Link>
            <h1 className="font-display mb-2 text-3xl tracking-wide text-[#D4AF37]">
              {thread.title}
            </h1>
            <div className="flex items-center gap-2 text-sm text-[#a8a6a3]">
              <span>{thread.village}</span>
              <span>•</span>
              <span>{thread.replies} replies</span>
              <span>•</span>
              <span>Last activity {thread.lastPost}</span>
            </div>
          </div>

          {/* Posts */}
          <div className="mb-6 space-y-6">
            {threadPosts.map((post) => (
              <div 
                key={post.id}
                className="glow-gold overflow-hidden rounded-xl border border-[#2a2a2a] bg-[#1a1a1a]"
              >
                {/* Post Header */}
                <div 
                  className="border-b px-6 py-4"
                  style={{ 
                    borderColor: post.author.cityColor + '40',
                    backgroundColor: post.author.cityColor + '08'
                  }}
                >
                  <div className="flex items-center gap-4">
                    <Link to={`/character/${post.author.id}`}>
                      <div 
                        className="flex h-12 w-12 items-center justify-center rounded-full border-2 transition-transform hover:scale-105"
                        style={{ 
                          borderColor: post.author.cityColor,
                          background: `linear-gradient(135deg, ${post.author.cityColor}aa, ${post.author.cityColor}66)`
                        }}
                      >
                        <span className="text-sm text-[#e8e6e3]">{post.author.name[0]}</span>
                      </div>
                    </Link>
                    <div className="flex-1">
                      <Link 
                        to={`/character/${post.author.id}`}
                        className="font-display text-[#D4AF37] hover:text-[#B8941F]"
                      >
                        {post.author.name}
                      </Link>
                      <div className="flex items-center gap-2 text-sm text-[#a8a6a3]">
                        <span>{post.author.class}</span>
                        <span>•</span>
                        <span>Lv. {post.author.level}</span>
                        <span>•</span>
                        <span style={{ color: post.author.cityColor }}>{post.author.cityName}</span>
                      </div>
                    </div>
                    <span className="text-xs text-[#686664]">{post.timestamp}</span>
                  </div>
                </div>

                {/* Post Content */}
                <div className="p-6">
                  <div className="prose prose-invert max-w-none">
                    {post.content.split('\n\n').map((paragraph, idx) => {
                      const isAction = paragraph.trim().startsWith('*');
                      return (
                        <p 
                          key={idx}
                          className={`mb-4 last:mb-0 ${
                            isAction 
                              ? 'italic text-[#B87333]' 
                              : 'text-[#e8e6e3]'
                          }`}
                        >
                          {paragraph}
                        </p>
                      );
                    })}
                  </div>
                </div>
              </div>
            ))}
          </div>

          {/* Reply Box */}
          <div className="glow-gold-strong rounded-xl border border-[#D4AF37]/30 bg-[#1a1a1a] p-6">
            <h3 className="font-display mb-4 text-lg text-[#D4AF37]">Continue the Tale</h3>
            <Textarea 
              placeholder="Write your response... Use *asterisks* for actions and descriptions."
              className="mb-4 min-h-[200px] resize-none border-[#2a2a2a] bg-[#141414] text-[#e8e6e3] placeholder:text-[#686664]"
            />
            <div className="flex items-center justify-between">
              <span className="text-xs text-[#a8a6a3]">
                Posting as {threads[0].author.name}
              </span>
              <Button className="bg-[#D4AF37] text-[#0f0f0f] hover:bg-[#B8941F]">
                <Send className="mr-2 h-4 w-4" />
                Post Reply
              </Button>
            </div>
          </div>
        </div>

        {/* Right: Character Sidebar */}
        <div className="lg:col-span-1">
          <CharacterModule />

          {/* Thread Participants */}
          <div className="mt-6 rounded-xl border border-[#2a2a2a] bg-[#1a1a1a] p-6">
            <h3 className="font-display mb-4 text-lg text-[#D4AF37]">Participants</h3>
            <div className="space-y-3">
              {Array.from(new Set(threadPosts.map(p => p.author.id))).map((authorId) => {
                const author = threadPosts.find(p => p.author.id === authorId)!.author;
                return (
                  <Link 
                    key={author.id}
                    to={`/character/${author.id}`}
                    className="flex items-center gap-3 rounded-lg p-2 transition-colors hover:bg-[#141414]"
                  >
                    <div 
                      className="flex h-10 w-10 items-center justify-center rounded-full border-2"
                      style={{ 
                        borderColor: author.cityColor,
                        background: `linear-gradient(135deg, ${author.cityColor}aa, ${author.cityColor}66)`
                      }}
                    >
                      <span className="text-xs text-[#e8e6e3]">{author.name[0]}</span>
                    </div>
                    <div className="flex-1">
                      <div className="text-sm text-[#e8e6e3]">{author.name}</div>
                      <div className="text-xs text-[#a8a6a3]">{author.class}</div>
                    </div>
                  </Link>
                );
              })}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
