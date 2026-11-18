<?php
/**
 * Customer Registration Screen
 */
?>
<div class="max-w-md mx-auto px-4 py-16 sm:py-24">
    <div class="bg-white border border-subtleBorder rounded-3xl p-8 sm:p-10 shadow-sm space-y-6">
        <div class="text-center space-y-1">
            <span class="text-[10px] font-bold tracking-[0.25em] text-champagne uppercase">Private Membership</span>
            <h1 class="font-serif text-3xl font-bold text-obsidian">Create Account</h1>
            <p class="text-xs text-mutedText">Enjoy personalized sizing, order history, and exclusive capsule access.</p>
        </div>

        <form action="/register" method="POST" class="space-y-4 text-xs">
            <div>
                <label class="block font-semibold text-obsidian mb-1">Full Name *</label>
                <input type="text" name="name" required placeholder="Aarav Sharma"
                       class="w-full px-3.5 py-2.5 bg-[#FAF9F6] border border-subtleBorder rounded-lg focus:outline-none focus:border-obsidian">
            </div>

            <div>
                <label class="block font-semibold text-obsidian mb-1">Email Address *</label>
                <input type="email" name="email" required placeholder="aarav@example.com"
                       class="w-full px-3.5 py-2.5 bg-[#FAF9F6] border border-subtleBorder rounded-lg focus:outline-none focus:border-obsidian">
            </div>

            <div>
                <label class="block font-semibold text-obsidian mb-1">Mobile Phone (Optional)</label>
                <input type="tel" name="phone" placeholder="+91 98765 43210"
                       class="w-full px-3.5 py-2.5 bg-[#FAF9F6] border border-subtleBorder rounded-lg focus:outline-none focus:border-obsidian">
            </div>

            <div>
                <label class="block font-semibold text-obsidian mb-1">Password * (Min 8 Characters)</label>
                <input type="password" name="password" required minlength="8" placeholder="••••••••"
                       class="w-full px-3.5 py-2.5 bg-[#FAF9F6] border border-subtleBorder rounded-lg focus:outline-none focus:border-obsidian">
            </div>

            <button type="submit" class="w-full py-3.5 bg-obsidian text-white text-xs font-bold uppercase tracking-[0.2em] rounded-xl hover:bg-slateDark transition-all shadow-md">
                Register & Join Atelier
            </button>
        </form>

        <div class="text-center text-xs text-mutedText pt-2 border-t border-subtleBorder">
            Already have an account? 
            <a href="/login" class="text-obsidian font-bold underline ml-1">Sign In</a>
        </div>
    </div>
</div>

