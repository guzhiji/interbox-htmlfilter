
interbox.core.util.HTMLFilter is a simplified html XSS filter with pretty good performance due to its independence on heavy regular expressions (the Java implementation partially depends on regular expressions). It is designed to remove all html elements and attributes out of the pre-configured white-list.

For performance reasons, this utility does not guarantee standard compliant output if the input does not - this means that the input html has to conform to html standard. The code here simply makes dangerous code impossible to be executed without considering a good-looking output.

NOTE: this utility is UNDER EXPERIMENT.

Welcome feedback! Thanks! (gu_zhiji@163.com)

这是一个简单的XSS过滤器。因为没有使用正则表达式（Java版部分依赖正则表达式），所以性能比较高。根据设计，它可以移除所有白名单之外的HTML元素和属性。

由于追求性能，如果输入不符合HTML标准，此工具不能保证标准的HTML输出。这意味着，输入必须遵从HTML标准，否则，只使危险代码无法执行，而不考虑HTML格式。

注意：本工具仍然处于实验阶段！

欢迎反馈您的测试结果！感谢您的参与！ (gu_zhiji@163.com)


