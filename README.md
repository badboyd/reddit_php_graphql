### Run locally
```
php -S localhost:8080 ./graphql.php
```

### Docker
```
docker build -t reddit_php_graphql .
docker run -p 8080:80 reddit_php_graphql
```

### Schema
```
	query {
		subreddit(name, limit, after, before, interval, sort, type, query){
			title
			url
			author
			score
			fullnameId
			numComments
			thumbnail
			subreddit
			created_utc
		}
	}
```

### Try query
```
curl -X POST \
  http://127.0.0.1:8080 \
  -H 'Content-Type: application/json' \
  -d '{
    "query": "query {subreddit{title, url, author, score, name, subreddit, id, thumbnail}}"
}'
```
