SELECT *
FROM `users`
INNER JOIN `objects`
ON users.object_id = objects.id
