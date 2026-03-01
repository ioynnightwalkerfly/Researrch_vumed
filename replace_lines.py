import sys

def replace_lines(src_filename, dest_filename):
    with open(src_filename, 'r', encoding='utf-8') as f:
        lines = f.readlines()
    
    # We apply replacements bottom-up to avoid line number shifting
    replacements = [
        (726, 792, "    <?php require_once 'includes/footer.php'; ?>\n"),
        (543, 598, "                <?php require_once 'components/tab_activities.php'; ?>\n"),
        (499, 541, "                <?php require_once 'components/tab_ethics.php'; ?>\n"),
        (402, 497, "                <?php require_once 'components/tab_pr_gallery.php'; ?>\n"),
        (371, 400, "                <?php require_once 'components/tab_publications.php'; ?>\n"),
        (265, 369, "                <?php require_once 'components/tab_overview.php'; ?>\n"),
        (196, 204, "        <?php require_once 'includes/hero_section.php'; ?>\n"),
        (167, 190, "    <?php require_once 'includes/navbar.php'; ?>\n"),
        (5, 142, "    <?php require_once 'includes/header_scripts.php'; ?>\n")
    ]
    
    for start, end, text in replacements:
        # start and end are 1-indexed. Convert to 0-indexed logic.
        start_idx = start - 1
        end_idx = end
        lines[start_idx:end_idx] = [text]
        
    with open(dest_filename, 'w', encoding='utf-8') as f:
        f.writelines(lines)

if __name__ == '__main__':
    replace_lines('index.html', 'index.php')
