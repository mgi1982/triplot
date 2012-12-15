---
title: Azure Sharding with Doctrine
layout: default
---

# Sharding

You can use SQL Azure Federations by enabling a shard manager for a Doctrine DBAL
connection. Specifiy the `federations` key and then keys with the corre

    windows_azure_distribution:
        federations:
            default:
                federationName: User_Federation
                distributionKey: user_id
                distributionType: guid

You can have one federation per connection at the moment.

You can use the shard manager service with the service-id 'windows_azure_distribution.shard_manager' for the default
and `windows_azure_distribution.<connectionname>_shard_manager` for any connection shard manager.

See the [Doctrine Shards](https://github.com/doctrine/shards) Documentation for more information about how to use Azure Federations.
