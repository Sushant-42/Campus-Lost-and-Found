# Network Analysis: Wikipedia Home Page

**Course:** CS543 - Web Services
**Assignment:** 1 - HTTP by Hand & Project Setup
**Project:** Student Lost & Found Management System
**Team ID:** 5
**Team members:** Sushant Prashant Prabhavalakar (20251651093), Harsh Yadav (20251651042)

---

## 1. Objective

The objective of this task is to observe how a real website loads over HTTP by using the browser's developer tools. The Network panel lists every request the browser makes, how large each response is, and how long each one takes. Reading this data builds the understanding needed to design efficient web services later in the Student Lost & Found project.

## 2. Test Setup

| Item | Value |
|---|---|
| Website tested | https://www.wikipedia.org/ |
| Browser | Microsoft Edge |
| Browser version | <<FILL: Edge -> Settings -> About Microsoft Edge>> |
| Tool used | DevTools -> Network tab |
| Cache setting | "Disable cache" enabled |
| Date and time of test | <<FILL: date and time>> |
| Screenshot (optional) | <<FILL: file name, for example docs/screenshots/wikipedia-network.png>> |

## 3. Testing Procedure

1. Opened Microsoft Edge and pressed `F12` to open DevTools.
2. Selected the **Network** tab.
3. Ticked **Disable cache** so that every resource is fetched from the network instead of the browser cache.
4. Loaded https://www.wikipedia.org/ and waited for the page to finish loading.
5. Read the summary bar at the bottom of the Network panel for the request count, transferred size, resources size and finish time.

## 4. Network Summary

These values were recorded from the browser.

| Measurement | Recorded value |
|---|---|
| Total requests | 6 |
| Transferred data | 101 KB |
| Resources size | 242 KB |
| Page load finish time | 711 ms |

## 5. Meaning of the Measurements

- **Request count:** the number of separate HTTP requests the browser sent to load the page. The page itself is one request, and each additional file it needs (for example a stylesheet, script or image) is another request.
- **Transferred size:** the amount of data that actually travelled over the network, including response headers. If the server compresses a response (for example with gzip), only the compressed size is counted here.
- **Resources size:** the total size of the resources after they have been decompressed. It shows how much content the browser has to process, regardless of how much was sent over the network.

## 6. Observations

- The page was loaded with 6 requests and finished in 711 ms.
- The transferred size (101 KB) is smaller than the resources size (242 KB). This is consistent with some of the content being compressed during transfer, but compression was not checked individually, so this is an interpretation and not a confirmed finding.
- Per-request details (which file was slowest, and whether any request returned a 3xx or 4xx status) have not been recorded yet and are left as placeholders below.

## 7. Slowest Resource

To find it: in the Network tab, click the **Time** column header to sort, then take the top row.

| Field | Value |
|---|---|
| Resource name / URL | <<FILL: name of the slowest resource>> |
| Resource type | <<FILL: for example document, script, image, stylesheet>> |
| Duration | <<FILL: time shown in the Time column>> |

## 8. HTTP 3xx and 4xx Responses

To find them: sort the **Status** column, or type `status-code:3` and `status-code:4` into the filter box.

| Status code | Resource | Notes |
|---|---|---|
| <<FILL: status code, or "none">> | <<FILL: resource name, or "none">> | <<FILL: notes>> |

<<FILL: if no 3xx or 4xx responses were present, replace this table with a sentence stating that none were observed. Write that only after checking.>>

## 9. Conclusion

Loading the Wikipedia home page with the cache disabled required 6 requests, transferred 101 KB of data (242 KB once decompressed) and finished in 711 ms. These figures show how request count and payload size affect load time, which is useful background for the Student Lost & Found services, where keeping responses small and requests few will matter when the APIs are designed.