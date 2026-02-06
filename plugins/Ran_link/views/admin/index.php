<div class="md:pl-64 flex flex-col min-h-screen transition-all duration-300">
    <main class="p-8 flex-1">
        <div class="mb-6 flex items-center gap-4">
             <button onclick="toggleSidebar()" class="md:hidden text-gray-500 hover:text-black focus:outline-none">
                <i class="fas fa-bars text-xl"></i>
            </button>
             <h2 class="text-2xl font-bold text-gray-800">友情链接管理</h2>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Add Form -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden h-fit">
                <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-plus-circle text-blue-500"></i> 添加链接
                    </h3>
                </div>
                <form action="<?= url('/admin/links/save') ?>" method="POST" class="p-6 space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">站点名称</label>
                        <input type="text" name="name" required class="w-full bg-gray-50 border-gray-200 rounded-lg px-4 py-3 text-sm focus:ring-black focus:border-black transition-all" placeholder="RanUI Blog">
                    </div>
                     <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">站点链接</label>
                        <input type="url" name="url" required class="w-full bg-gray-50 border-gray-200 rounded-lg px-4 py-3 text-sm focus:ring-black focus:border-black transition-all" placeholder="https://...">
                    </div>
                    <button type="submit" class="w-full bg-black text-white px-4 py-3 rounded-lg text-sm font-bold hover:bg-gray-800 transition-colors">
                        添加 / 保存
                    </button>
                </form>
            </div>

            <!-- List -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Site Name</th>
                                <th class="px-6 py-4">URL</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php foreach($links as $link): ?>
                            <tr class="hover:bg-gray-50 transition-colors group">
                                <td class="px-6 py-4">
                                    <?php if($link['status'] == 1): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-50 text-green-700">
                                        Active
                                    </span>
                                    <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-50 text-yellow-700 animate-pulse">
                                        Pending
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-800"><?= e($link['name']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate"><?= e($link['url']) ?></td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <?php if($link['status'] == 0): ?>
                                    <a href="<?= url('/admin/links/approve/' . $link['id']) ?>" class="text-green-500 hover:text-green-700 transition-colors text-xs font-bold">
                                        <i class="fas fa-check"></i> 审核
                                    </a>
                                    <?php endif; ?>
                                    <a href="<?= url('/admin/links/delete/' . $link['id']) ?>" onclick="return confirm('Ensure delete?')" class="text-red-400 hover:text-red-600 transition-colors text-xs font-bold">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($links)): ?>
                            <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400 text-sm">暂无数据</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
