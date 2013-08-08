# convert posts to products
SELECT
	post_title, term.name, tax.parent, tax.taxonomy,  post.post_type
FROM wp_posts AS post 
RIGHT JOIN wp_term_relationships as rel on post.ID = rel.object_id 
LEFT JOIN wp_terms AS term ON rel.term_taxonomy_id = term.term_id
LEFT JOIN wp_term_taxonomy AS tax ON rel.term_taxonomy_id = tax.term_id
WHERE 1
AND post_type = 'post'
AND tax.taxonomy = 'category';

UPDATE wp_posts AS post
RIGHT JOIN wp_term_relationships as rel on post.ID = rel.object_id 
LEFT JOIN wp_terms AS term ON rel.term_taxonomy_id = term.term_id
LEFT JOIN wp_term_taxonomy AS tax ON rel.term_taxonomy_id = tax.term_id
SET post_type = 'product'
WHERE 1
AND term.name != 'news'
AND post_type = 'post'
AND tax.taxonomy = 'category';
